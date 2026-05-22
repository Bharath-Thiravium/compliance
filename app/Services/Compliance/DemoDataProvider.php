<?php

namespace App\Services\Compliance;

use Carbon\Carbon;

class DemoDataProvider
{
    public static function for(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $records = match($formCode) {
            'FORM_XVI', 'FORM_XVII', 'FORM_XIX', 'FORM_XXI', 'FORM_XX', 'FORM_XXII', 'FORM_XXIII' => self::clraRecords(),
            'FORM_8', 'FORM_11', 'FORM_26' => self::incidentRecords(),
            'FORM_12', 'FORM_17', 'FORM_18' => self::employeeRecords(),
            'FORM_2' => self::attendanceRecords(),
            'FORM_XII', 'CONTRACTOR_MASTER' => self::contractorRecords(),
            'FORM_XIII' => self::workmenRecords(),
            'FORM_XXIV', 'FORM_XXV', 'CLRA_RETURN' => self::clraReturnRecords(),
            'CLRA_LICENSE' => self::licenseRecords(),
            default => collect([])
        };

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period_month' => $month,
            'period_year' => $year,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'records' => $records,
            'config' => config("compliance_forms.{$formCode}", []),
        ];
    }

    private static function clraRecords()
    {
        $records = [];
        for ($i = 1; $i <= 30; $i++) {
            $basicEarned = rand(12000, 25000);
            $daEarned = rand(2000, 5000);
            $hraEarned = rand(1500, 3000);
            $overtimeWages = rand(0, 3000);
            $grossSalary = $basicEarned + $daEarned + $hraEarned + $overtimeWages;
            $pfEmployee = round($grossSalary * 0.12);
            $esiEmployee = round($grossSalary * 0.0075);
            $advances = rand(0, 2000);
            $fines = rand(0, 500);
            $totalDeductions = $pfEmployee + $esiEmployee + $advances + $fines;
            $netSalary = $grossSalary - $totalDeductions;
            
            $records[] = (object)[
                'id' => $i,
                'employee_id' => $i,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'employee_name' => self::randomName(),
                'name' => self::randomName(),
                'designation' => self::randomDesignation(),
                'contractor_name' => 'ABC Contractors Pvt Ltd',
                'contractor_id' => rand(1, 5),
                'wage_rate' => rand(400, 800),
                'basic_earned' => $basicEarned,
                'da_earned' => $daEarned,
                'hra_earned' => $hraEarned,
                'overtime_hours' => rand(0, 20),
                'overtime_wages' => $overtimeWages,
                'gross_salary' => $grossSalary,
                'pf_employee' => $pfEmployee,
                'esi_employee' => $esiEmployee,
                'esi_number' => 'ESI' . rand(10000000, 99999999),
                'pf_number' => 'PF/TN/' . rand(100000, 999999) . '/' . rand(1000, 9999),
                'advances' => $advances,
                'fines' => $fines,
                'fine_reason' => $fines > 0 ? 'Late arrival / Absence without notice' : '',
                'damage_amount' => rand(0, 1000),
                'damage_description' => 'Equipment damage during operation',
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'total_days_worked' => rand(22, 26),
                'deployment_start' => now()->subMonths(rand(1, 12))->format('Y-m-d'),
                'deployment_end' => now()->addMonths(rand(1, 6))->format('Y-m-d'),
                'work_order' => 'WO' . rand(1000, 9999),
                'work_order_number' => 'WO' . rand(1000, 9999),
            ];
        }
        return collect($records);
    }

    private static function incidentRecords()
    {
        $types = ['Minor Injury', 'Serious Accident', 'Dangerous Occurrence', 'Near Miss', 'Equipment Failure'];
        $records = [];
        for ($i = 1; $i <= 8; $i++) {
            $incidentType = $types[array_rand($types)];
            $records[] = (object)[
                'id' => $i,
                'employee_id' => $i,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'employee_name' => self::randomName(),
                'name' => self::randomName(),
                'designation' => self::randomDesignation(),
                'incident_type' => $incidentType,
                'incident_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                'location' => 'Production Floor ' . rand(1, 5),
                'description' => 'Incident: ' . $incidentType . '. Occurred during routine operations. Immediate first aid provided. Safety officer notified.',
                'authority_name' => 'Factory Inspector - District Labour Office',
                'reference_number' => 'REF/' . date('Y') . '/' . rand(1000, 9999),
                'severity' => rand(1, 3) == 1 ? 'Minor' : (rand(1, 2) == 1 ? 'Serious' : 'Critical'),
                'action_taken' => 'Medical assistance provided, area cordoned, investigation initiated',
            ];
        }
        return collect($records);
    }

    private static function employeeRecords()
    {
        $records = [];
        for ($i = 1; $i <= 40; $i++) {
            $age = rand(18, 55);
            $records[] = (object)[
                'id' => $i,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'employee_name' => self::randomName(),
                'name' => self::randomName(),
                'designation' => self::randomDesignation(),
                'date_of_joining' => now()->subYears(rand(1, 10))->format('Y-m-d'),
                'date_of_birth' => now()->subYears($age)->format('Y-m-d'),
                'age' => $age,
                'pf_number' => 'PF/TN/' . rand(100000, 999999) . '/' . rand(1000, 9999),
                'esi_number' => 'ESI' . rand(10000000, 99999999),
                'department' => self::randomDepartment(),
                'basic_salary' => rand(15000, 35000),
                'father_name' => self::randomName(),
                'address' => rand(1, 999) . ', ' . self::randomStreet() . ', Chennai - ' . rand(600001, 600099),
                'gender' => rand(1, 2) == 1 ? 'Male' : 'Female',
            ];
        }
        return collect($records);
    }
    
    private static function contractorRecords()
    {
        $records = [];
        $contractors = [
            'ABC Contractors Pvt Ltd',
            'XYZ Labour Services',
            'Prime Workforce Solutions',
            'Elite Manpower Services',
            'Global Labour Contractors'
        ];
        
        for ($i = 1; $i <= 5; $i++) {
            $records[] = (object)[
                'id' => $i,
                'company_name' => $contractors[$i - 1],
                'license_number' => 'CLRA/TN/' . rand(1000, 9999) . '/' . date('Y'),
                'valid_from' => now()->subMonths(rand(1, 12))->format('Y-m-d'),
                'valid_to' => now()->addYears(rand(1, 3))->format('Y-m-d'),
                'contact_person' => self::randomName(),
                'contact_number' => '+91 ' . rand(70000, 99999) . rand(10000, 99999),
                'address' => rand(1, 99) . ', Industrial Area, Chennai - ' . rand(600001, 600099),
                'registration_number' => 'REG/' . rand(10000, 99999),
            ];
        }
        return collect($records);
    }
    
    private static function workmenRecords()
    {
        $records = [];
        for ($i = 1; $i <= 35; $i++) {
            $records[] = (object)[
                'id' => $i,
                'employee_id' => $i,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'worker_name' => self::randomName(),
                'employee_name' => self::randomName(),
                'name' => self::randomName(),
                'designation' => self::randomDesignation(),
                'contractor_id' => rand(1, 5),
                'contractor_name' => 'ABC Contractors Pvt Ltd',
                'deployment_start' => now()->subMonths(rand(1, 12))->format('Y-m-d'),
                'deployment_end' => now()->addMonths(rand(1, 6))->format('Y-m-d'),
                'wage_rate' => rand(400, 800),
                'work_order' => 'WO' . rand(1000, 9999),
                'work_order_number' => 'WO' . rand(1000, 9999),
                'esi_number' => 'ESI' . rand(10000000, 99999999),
                'pf_number' => 'PF/TN/' . rand(100000, 999999) . '/' . rand(1000, 9999),
            ];
        }
        return collect($records);
    }
    
    private static function clraReturnRecords()
    {
        $records = [];
        for ($i = 1; $i <= 3; $i++) {
            $records[] = (object)[
                'id' => $i,
                'period_from' => now()->subMonths(6)->startOfMonth()->format('Y-m-d'),
                'period_to' => now()->endOfMonth()->format('Y-m-d'),
                'total_workers' => rand(25, 50),
                'max_workers_any_day' => rand(30, 55),
                'total_mandays' => rand(500, 1200),
                'contractor_count' => rand(3, 7),
                'work_nature' => 'Manufacturing and Assembly Operations',
            ];
        }
        return collect($records);
    }
    
    private static function licenseRecords()
    {
        $records = [];
        for ($i = 1; $i <= 5; $i++) {
            $records[] = (object)[
                'id' => $i,
                'contractor_id' => $i,
                'license_number' => 'CLRA/TN/' . rand(1000, 9999) . '/' . date('Y'),
                'issue_date' => now()->subMonths(rand(1, 12))->format('Y-m-d'),
                'expiry_date' => now()->addYears(rand(1, 3))->format('Y-m-d'),
                'status' => 'Active',
            ];
        }
        return collect($records);
    }
    
    private static function randomStreet()
    {
        $streets = ['Anna Nagar', 'T Nagar', 'Velachery', 'Adyar', 'Guindy', 'Tambaram', 'Porur'];
        return $streets[array_rand($streets)];
    }

    private static function attendanceRecords()
    {
        $records = [];
        for ($i = 1; $i <= 30; $i++) {
            for ($day = 1; $day <= 26; $day++) {
                $records[] = (object)[
                    'id' => ($i * 100) + $day,
                    'employee_id' => $i,
                    'attendance_date' => now()->startOfMonth()->addDays($day - 1)->format('Y-m-d'),
                    'status' => rand(1, 10) > 2 ? 'present' : 'absent',
                ];
            }
        }
        return collect($records);
    }

    private static function randomName()
    {
        $names = ['Rajesh Kumar', 'Priya Sharma', 'Amit Patel', 'Sunita Reddy', 'Vijay Singh', 
                  'Anita Desai', 'Ravi Verma', 'Kavita Nair', 'Suresh Gupta', 'Meena Iyer'];
        return $names[array_rand($names)];
    }

    private static function randomDesignation()
    {
        $designations = ['Operator', 'Supervisor', 'Technician', 'Helper', 'Skilled Worker', 
                         'Machine Operator', 'Quality Inspector', 'Maintenance Staff'];
        return $designations[array_rand($designations)];
    }

    private static function randomDepartment()
    {
        $departments = ['Production', 'Maintenance', 'Quality Control', 'Packaging', 'Assembly'];
        return $departments[array_rand($departments)];
    }
}
