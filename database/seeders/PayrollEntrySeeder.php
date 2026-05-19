<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollEntrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [];
        $entryId = 1;

        // Get all employees and payroll cycles
        $employees = DB::table('workforce_employee')->get();
        $cycles = DB::table('workforce_payroll_cycle')->get();

        foreach ($cycles as $cycle) {
            // Get employees for this tenant
            $tenantEmployees = $employees->where('tenant_id', $cycle->tenant_id);

            foreach ($tenantEmployees as $employee) {
                $basicSalary = $employee->basic_salary;
                $daysWorked = rand(20, 26);
                $basicEarned = ($basicSalary / 26) * $daysWorked;
                $daEarned = ($basicSalary * 0.15 / 26) * $daysWorked;
                $hraEarned = ($basicSalary * 0.10 / 26) * $daysWorked;
                $overtimeWages = rand(0, 5000);
                $grossSalary = $basicEarned + $daEarned + $hraEarned + $overtimeWages;

                $pfEmployee = $basicEarned * 0.12;
                $esiEmployee = $basicEarned * 0.0075;
                $professionalTax = $basicSalary > 15000 ? 200 : 0;
                $fines = rand(0, 1000);
                $advances = rand(0, 5000);
                $totalDeductions = $pfEmployee + $esiEmployee + $professionalTax + $fines + $advances;
                $netSalary = $grossSalary - $totalDeductions;

                $entries[] = [
                    'id' => $entryId++,
                    'tenant_id' => $cycle->tenant_id,
                    'branch_id' => $employee->branch_id,
                    'employee_id' => $employee->id,
                    'payroll_cycle_id' => $cycle->id,
                    'total_days_worked' => $daysWorked,
                    'paid_leave_days' => rand(0, 2),
                    'unpaid_leave_days' => 26 - $daysWorked,
                    'overtime_hours' => rand(0, 20),
                    'basic_earned' => round($basicEarned, 2),
                    'da_earned' => round($daEarned, 2),
                    'hra_earned' => round($hraEarned, 2),
                    'other_allowances' => 0,
                    'overtime_wages' => round($overtimeWages, 2),
                    'gross_salary' => round($grossSalary, 2),
                    'pf_employee' => round($pfEmployee, 2),
                    'esi_employee' => round($esiEmployee, 2),
                    'professional_tax' => $professionalTax,
                    'fines' => $fines,
                    'advances' => $advances,
                    'other_deductions' => 0,
                    'total_deductions' => round($totalDeductions, 2),
                    'net_salary' => round($netSalary, 2),
                    'payment_date' => now()->toDateString(),
                    'payment_mode' => 'Bank Transfer',
                    'transaction_reference' => 'TXN' . str_pad($entryId, 10, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Batch insert every 100 records
                if (count($entries) >= 100) {
                    DB::table('workforce_payroll_entry')->insert($entries);
                    $entries = [];
                }
            }
        }

        // Insert remaining records
        if (!empty($entries)) {
            DB::table('workforce_payroll_entry')->insert($entries);
        }
    }
}
