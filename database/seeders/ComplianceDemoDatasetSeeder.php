<?php

namespace Database\Seeders;

use App\Models\WorkforceEmployee;
use App\Models\WorkforceAttendance;
use App\Models\PayrollEntry;
use App\Models\PayrollCycle;
use App\Models\Contractor;
use App\Models\ContractLabourDeployment;
use App\Models\IncidentDocument;
use App\Models\HazardRegister;
use App\Models\EmployeeFinancialRegister;
use App\Models\BonusRecord;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ComplianceDemoDatasetSeeder extends Seeder
{
    private const TENANT_ID = 1;
    private const BRANCH_ID = 1;

    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['id' => self::TENANT_ID], [
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
        ]);

        $branch = Branch::firstOrCreate(['id' => self::BRANCH_ID], [
            'tenant_id' => self::TENANT_ID,
            'name' => 'Main Branch',
            'code' => 'MB001',
            'address' => '123 Industrial Area, City',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600001',
        ]);

        $this->seedEmployees();
        $this->seedAttendance();
        $this->seedPayrollCycle();
        $this->seedPayrollEntries();
        $this->seedContractors();
        $this->seedContractLabourDeployment();
        $this->seedIncidents();
        $this->seedHazardRegister();
        $this->seedFinancialRegister();
        $this->seedBonusRecords();
        $this->seedLeaves();
        $this->seedHolidays();
    }

    private function seedEmployees(): void
    {
        $designations = ['Manager', 'Supervisor', 'Operator', 'Helper', 'Technician', 'Clerk', 'Driver', 'Security'];
        $departments = ['Production', 'Maintenance', 'Quality', 'HR', 'Finance', 'Admin', 'Logistics'];

        for ($i = 1; $i <= 50; $i++) {
            WorkforceEmployee::firstOrCreate(
                ['employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT)],
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'name' => 'Employee ' . $i,
                    'pf_number' => 'PF' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'esi_number' => 'ESI' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'date_of_joining' => Carbon::now()->subYears(rand(1, 5))->subDays(rand(0, 365)),
                    'designation' => $designations[array_rand($designations)],
                    'department' => $departments[array_rand($departments)],
                    'basic_salary' => rand(15000, 50000),
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedAttendance(): void
    {
        $employees = WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        $statuses = ['present', 'absent', 'leave', 'half_day'];
        $startDate = Carbon::now()->subMonths(3);

        for ($i = 0; $i < 1500; $i++) {
            $date = $startDate->copy()->addDays(rand(0, 90));
            if ($date->isWeekend()) continue;

            WorkforceAttendance::firstOrCreate(
                [
                    'employee_id' => $employees->random(),
                    'attendance_date' => $date,
                ],
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'status' => $statuses[array_rand($statuses)],
                    'remarks' => rand(0, 1) ? 'Regular' : null,
                ]
            );
        }
    }

    private function seedPayrollCycle(): void
    {
        for ($month = 1; $month <= 3; $month++) {
            PayrollCycle::firstOrCreate(
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'month' => $month,
                    'year' => 2024,
                ],
                [
                    'cycle_name' => 'Month ' . $month . ' 2024',
                    'start_date' => Carbon::createFromDate(2024, $month, 1),
                    'end_date' => Carbon::createFromDate(2024, $month, 1)->endOfMonth(),
                    'status' => 'closed',
                ]
            );
        }
    }

    private function seedPayrollEntries(): void
    {
        $employees = WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        $cycles = PayrollCycle::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->get();

        foreach ($cycles as $cycle) {
            foreach ($employees as $employeeId) {
                $basicSalary = rand(15000, 50000);
                $da = $basicSalary * 0.15;
                $hra = $basicSalary * 0.10;
                $grossSalary = $basicSalary + $da + $hra + rand(1000, 5000);
                $deductions = $grossSalary * 0.20;

                PayrollEntry::firstOrCreate(
                    [
                        'payroll_cycle_id' => $cycle->id,
                        'employee_id' => $employeeId,
                    ],
                    [
                        'total_days_worked' => rand(20, 26),
                        'paid_leave_days' => rand(0, 2),
                        'unpaid_leave_days' => rand(0, 1),
                        'overtime_hours' => rand(0, 20),
                        'basic_earned' => $basicSalary,
                        'da_earned' => $da,
                        'hra_earned' => $hra,
                        'other_allowances' => rand(1000, 5000),
                        'overtime_wages' => rand(0, 2000),
                        'gross_salary' => $grossSalary,
                        'pf_employee' => $basicSalary * 0.12,
                        'esi_employee' => $basicSalary * 0.0075,
                        'professional_tax' => 200,
                        'fines' => rand(0, 500),
                        'advances' => rand(0, 2000),
                        'other_deductions' => rand(0, 1000),
                        'total_deductions' => $deductions,
                        'net_salary' => $grossSalary - $deductions,
                        'payment_date' => $cycle->end_date->addDays(5),
                        'payment_mode' => 'bank_transfer',
                    ]
                );
            }
        }
    }

    private function seedContractors(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Contractor::firstOrCreate(
                ['contractor_code' => 'CTR' . str_pad($i, 4, '0', STR_PAD_LEFT)],
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'name' => 'Contractor ' . $i,
                    'registration_number' => 'REG' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'address' => 'Address ' . $i,
                    'phone' => '98' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'email' => 'contractor' . $i . '@example.com',
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedContractLabourDeployment(): void
    {
        $contractors = Contractor::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        for ($i = 0; $i < 30; $i++) {
            ContractLabourDeployment::firstOrCreate(
                [
                    'contractor_id' => $contractors->random(),
                    'deployment_date' => Carbon::now()->subDays(rand(0, 90)),
                ],
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'number_of_workers' => rand(5, 20),
                    'work_description' => 'Contract work ' . $i,
                    'location' => 'Location ' . $i,
                    'overtime_hours' => rand(0, 50),
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedIncidents(): void
    {
        $employees = WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        $types = ['Minor Injury', 'Major Injury', 'Near Miss', 'Property Damage'];

        for ($i = 0; $i < 10; $i++) {
            IncidentDocument::firstOrCreate(
                [
                    'incident_code' => 'INC' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                ],
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'employee_id' => $employees->random(),
                    'incident_date' => Carbon::now()->subDays(rand(0, 90)),
                    'incident_type' => $types[array_rand($types)],
                    'description' => 'Incident description ' . $i,
                    'location' => 'Location ' . $i,
                    'severity' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                    'status' => 'closed',
                ]
            );
        }
    }

    private function seedHazardRegister(): void
    {
        $hazardTypes = ['Chemical', 'Electrical', 'Mechanical', 'Thermal', 'Biological'];
        $severities = ['low', 'medium', 'high', 'critical'];

        for ($i = 0; $i < 5; $i++) {
            HazardRegister::firstOrCreate(
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'hazard_date' => Carbon::now()->subDays(rand(0, 90)),
                    'hazard_type' => $hazardTypes[array_rand($hazardTypes)],
                ],
                [
                    'description' => 'Hazard description ' . $i,
                    'location' => 'Location ' . $i,
                    'severity' => $severities[array_rand($severities)],
                    'status' => 'mitigated',
                    'corrective_action' => 'Action taken ' . $i,
                    'action_date' => Carbon::now()->subDays(rand(0, 30)),
                ]
            );
        }
    }

    private function seedFinancialRegister(): void
    {
        $employees = WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        $types = ['loan', 'fine', 'advance'];
        $reasons = ['Personal Loan', 'Festival Advance', 'Disciplinary Fine', 'Medical Advance'];

        for ($i = 0; $i < 20; $i++) {
            EmployeeFinancialRegister::firstOrCreate(
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'employee_id' => $employees->random(),
                    'transaction_date' => Carbon::now()->subDays(rand(0, 90)),
                    'transaction_type' => $types[array_rand($types)],
                ],
                [
                    'amount' => rand(5000, 50000),
                    'reason' => $reasons[array_rand($reasons)],
                    'status' => 'active',
                    'installments' => rand(3, 12),
                    'installment_amount' => rand(1000, 5000),
                ]
            );
        }
    }

    private function seedBonusRecords(): void
    {
        $employees = WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        foreach ($employees as $employeeId) {
            BonusRecord::firstOrCreate(
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'employee_id' => $employeeId,
                    'bonus_month' => 12,
                    'bonus_year' => 2024,
                ],
                [
                    'bonus_amount' => rand(5000, 20000),
                    'status' => 'paid',
                    'payment_date' => Carbon::createFromDate(2024, 12, 31),
                ]
            );
        }
    }

    private function seedLeaves(): void
    {
        $employees = WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->pluck('id');

        $types = ['Casual', 'Earned', 'Sick', 'Maternity'];

        for ($i = 0; $i < 30; $i++) {
            $from = Carbon::now()->subMonths(3)->addDays(rand(0, 90));
            $to = $from->copy()->addDays(rand(1, 5));

            EmployeeLeave::firstOrCreate(
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'employee_id' => $employees->random(),
                    'leave_from' => $from,
                ],
                [
                    'leave_to' => $to,
                    'leave_type' => $types[array_rand($types)],
                    'days' => $to->diffInDays($from) + 1,
                    'reason' => 'Leave reason ' . $i,
                    'status' => 'approved',
                ]
            );
        }
    }

    private function seedHolidays(): void
    {
        $holidays = [
            ['date' => '2024-01-26', 'name' => 'Republic Day'],
            ['date' => '2024-03-08', 'name' => 'Maha Shivaratri'],
            ['date' => '2024-03-25', 'name' => 'Holi'],
            ['date' => '2024-04-11', 'name' => 'Eid ul-Fitr'],
            ['date' => '2024-04-17', 'name' => 'Ram Navami'],
            ['date' => '2024-04-21', 'name' => 'Mahavir Jayanti'],
            ['date' => '2024-05-23', 'name' => 'Buddha Purnima'],
            ['date' => '2024-08-15', 'name' => 'Independence Day'],
            ['date' => '2024-09-16', 'name' => 'Milad un-Nabi'],
            ['date' => '2024-10-02', 'name' => 'Gandhi Jayanti'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                [
                    'tenant_id' => self::TENANT_ID,
                    'branch_id' => self::BRANCH_ID,
                    'holiday_date' => $holiday['date'],
                ],
                [
                    'holiday_name' => $holiday['name'],
                    'holiday_type' => 'national',
                ]
            );
        }
    }
}
