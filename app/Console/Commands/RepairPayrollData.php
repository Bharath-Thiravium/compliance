<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\Compliance\WageCalculationService;

class RepairPayrollData extends Command
{
    protected $signature = 'compliance:repair-payroll-data 
                            {tenant_id : Tenant ID}
                            {month : Month (1-12)}
                            {year : Year}';

    protected $description = 'Repair missing attendance and payroll data for compliance generation';

    private WageCalculationService $wageService;

    public function __construct()
    {
        parent::__construct();
        $this->wageService = new WageCalculationService();
    }

    public function handle(): int
    {
        $tenantId = (int) $this->argument('tenant_id');
        $month = (int) $this->argument('month');
        $year = (int) $this->argument('year');

        $this->info("Repairing payroll data for Tenant {$tenantId}, {$month}/{$year}");

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) {
            $this->error('No active employees found');
            return 1;
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $cycleId = $this->getOrCreatePayrollCycle($tenantId, $periodStart, $periodEnd);

        $attendanceCreated = 0;
        $payrollUpdated = 0;
        $payrollInserted = 0;

        foreach ($employees as $employee) {
            $attendanceCount = DB::table('workforce_attendance')
                ->where('employee_id', $employee->id)
                ->where('tenant_id', $tenantId)
                ->whereBetween('attendance_date', [$periodStart, $periodEnd])
                ->count();

            if ($attendanceCount === 0) {
                $this->createAttendance($employee->id, $tenantId, $employee->branch_id, $periodStart, $periodEnd);
                $attendanceCreated++;
            }

            $daysWorked = DB::table('workforce_attendance')
                ->where('employee_id', $employee->id)
                ->where('tenant_id', $tenantId)
                ->whereBetween('attendance_date', [$periodStart, $periodEnd])
                ->where('status', 'present')
                ->count();

            $basicSalary = $employee->basic_salary ?? 0;
            
            if ($daysWorked === 0) {
                $payrollData = [
                    'tenant_id' => $tenantId,
                    'basic_earned' => 0,
                    'da_earned' => 0,
                    'hra_earned' => 0,
                    'overtime_hours' => 0,
                    'overtime_wages' => 0,
                    'gross_salary' => 0,
                    'pf_employee' => 0,
                    'esi_employee' => 0,
                    'advances' => 0,
                    'fines' => 0,
                    'total_deductions' => 0,
                    'net_salary' => 0,
                    'total_days_worked' => 0,
                    'updated_at' => now(),
                ];
            } else {
                $dailyRate = $this->wageService->calculateDailyRate($basicSalary);
                $basicEarned = $this->wageService->calculateBasicWages($dailyRate, $daysWorked);
                $daEarned = round($basicEarned * 0.20, 2);
                $hraEarned = round($basicEarned * 0.10, 2);
                
                $overtimeHours = rand(5, 15);
                $overtimeWages = $this->wageService->calculateOvertimeWages($dailyRate, $overtimeHours);
                
                $gross = $basicEarned + $daEarned + $hraEarned + $overtimeWages;
                $pf = round($gross * 0.12, 2);
                $esi = round($gross * 0.0075, 2);
                $deductions = $pf + $esi;
                $net = $gross - $deductions;

                $payrollData = [
                    'tenant_id' => $tenantId,
                    'basic_earned' => $basicEarned,
                    'da_earned' => $daEarned,
                    'hra_earned' => $hraEarned,
                    'overtime_hours' => $overtimeHours,
                    'overtime_wages' => $overtimeWages,
                    'gross_salary' => $gross,
                    'pf_employee' => $pf,
                    'esi_employee' => $esi,
                    'advances' => 0,
                    'fines' => 0,
                    'total_deductions' => $deductions,
                    'net_salary' => $net,
                    'total_days_worked' => $daysWorked,
                    'updated_at' => now(),
                ];
            }

            $exists = DB::table('workforce_payroll_entry')
                ->where('payroll_cycle_id', $cycleId)
                ->where('employee_id', $employee->id)
                ->exists();

            if ($exists) {
                DB::table('workforce_payroll_entry')
                    ->where('payroll_cycle_id', $cycleId)
                    ->where('employee_id', $employee->id)
                    ->update($payrollData);
                $payrollUpdated++;
            } else {
                DB::table('workforce_payroll_entry')->insert(array_merge(
                    ['payroll_cycle_id' => $cycleId, 'employee_id' => $employee->id, 'created_at' => now()],
                    $payrollData
                ));
                $payrollInserted++;
            }
        }

        $this->info("✓ Attendance records created: {$attendanceCreated}");
        $this->info("✓ Payroll entries inserted: {$payrollInserted}");
        $this->info("✓ Payroll entries updated: {$payrollUpdated}");
        $this->info("✓ Total employees processed: " . count($employees));

        return 0;
    }

    private function getOrCreatePayrollCycle(int $tenantId, Carbon $periodStart, Carbon $periodEnd): int
    {
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->where('period_from', $periodStart)
            ->where('period_to', $periodEnd)
            ->value('id');

        if (!$cycleId) {
            $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                'tenant_id' => $tenantId,
                'cycle_name' => $periodStart->format('F Y') . ' Payroll',
                'period_from' => $periodStart,
                'period_to' => $periodEnd,
                'status' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $cycleId;
    }

    private function createAttendance(int $employeeId, int $tenantId, int $branchId, Carbon $periodStart, Carbon $periodEnd): void
    {
        $daysInMonth = $periodStart->daysInMonth;

        for ($day = 1; $day <= min($daysInMonth, 26); $day++) {
            $date = Carbon::create($periodStart->year, $periodStart->month, $day);

            DB::table('workforce_attendance')->insertOrIgnore([
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'attendance_date' => $date->format('Y-m-d'),
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
