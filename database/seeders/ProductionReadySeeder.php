<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProductionReadySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting Production Ready Seeding...');
        
        DB::statement('PRAGMA foreign_keys = OFF');
        
        // Clear all tables
        $this->clearAllData();
        
        // Create core data
        $tenantId = $this->createTenant();
        $branchId = $this->createBranch($tenantId);
        $userId = $this->createFullSubscriptionUser($tenantId);
        
        // Create operational data
        $this->createPayrollCycles($tenantId);
        $this->createEmployees($tenantId, $branchId);
        $this->createPayrollData($tenantId, $branchId);
        $this->createComplianceSections();
        $this->createComplianceForms();
        
        DB::statement('PRAGMA foreign_keys = ON');
        
        $this->command->info('✅ Production Ready Seeding Complete!');
        $this->command->info('');
        $this->command->info('📧 Login Credentials:');
        $this->command->info('   Email: admin@compliance.local');
        $this->command->info('   Password: password');
        $this->command->info('   Subscription: FULL');
    }
    
    private function clearAllData(): void
    {
        $this->command->info('🗑️  Clearing existing data...');
        
        DB::table('compliance_batch_forms')->truncate();
        DB::table('compliance_execution_batches')->truncate();
        DB::table('compliance_execution_logs')->truncate();
        DB::table('incident_documents')->truncate();
        DB::table('contract_labour_deployment')->truncate();
        DB::table('contractor_compliance')->truncate();
        DB::table('contractor_master')->truncate();
        DB::table('bonus_records')->truncate();
        DB::table('workforce_payroll_entry')->truncate();
        DB::table('workforce_payroll_cycle')->truncate();
        DB::table('workforce_employee')->truncate();
        DB::table('branches')->truncate();
        DB::table('tenants')->truncate();
        DB::table('users')->truncate();
        DB::table('compliance_forms_master')->truncate();
        DB::table('compliance_sections')->truncate();
        
        $this->command->info('✓ Data cleared');
    }
    
    private function createTenant(): int
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Compliance Industries Ltd',
            'subscription_type' => 'FULL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("✓ Created Tenant: {$tenantId}");
        return $tenantId;
    }
    
    private function createBranch(int $tenantId): int
    {
        $branchId = DB::table('branches')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_name' => 'Main Manufacturing Unit',
            'unit_name' => 'Manufacturing',
            'factory_license_number' => 'TN/FAC/2025/001',
            'address' => 'Industrial Area, Chennai - 600032',
            'pf_code' => 'TN/PF/2025/001',
            'esi_code' => 'TN/ESI/2025/001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("✓ Created Branch: {$branchId}");
        return $branchId;
    }
    
    private function createFullSubscriptionUser(int $tenantId): int
    {
        $userId = DB::table('users')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Admin User',
            'email' => 'admin@compliance.local',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("✓ Created FULL Subscription User: {$userId}");
        return $userId;
    }
    
    private function createPayrollCycles(int $tenantId): void
    {
        $months = [
            ['name' => 'January 2025', 'from' => '2025-01-01', 'to' => '2025-01-31'],
            ['name' => 'February 2025', 'from' => '2025-02-01', 'to' => '2025-02-28'],
            ['name' => 'March 2025', 'from' => '2025-03-01', 'to' => '2025-03-31'],
        ];
        
        foreach ($months as $month) {
            DB::table('workforce_payroll_cycle')->insert([
                'tenant_id' => $tenantId,
                'cycle_name' => $month['name'],
                'period_from' => $month['from'],
                'period_to' => $month['to'],
                'status' => 'processed',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("✓ Created 3 Payroll Cycles");
    }
    
    private function createEmployees(int $tenantId, int $branchId): void
    {
        $departments = ['Production', 'Maintenance', 'Quality', 'Packaging', 'Safety'];
        $designations = ['Supervisor', 'Technician', 'Machine Operator', 'Helper', 'Electrician'];
        
        for ($i = 1; $i <= 20; $i++) {
            DB::table('workforce_employee')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'pf_number' => 'PF/TN/2025/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'esi_number' => 'ESI/TN/2025/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date_of_joining' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                'designation' => $designations[$i % count($designations)],
                'department' => $departments[$i % count($departments)],
                'basic_salary' => 20000 + ($i * 1000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("✓ Created 20 Employees");
    }
    
    private function createPayrollData(int $tenantId, int $branchId): void
    {
        $cycles = DB::table('workforce_payroll_cycle')->where('tenant_id', $tenantId)->get();
        $employees = DB::table('workforce_employee')->where('tenant_id', $tenantId)->get();
        
        foreach ($cycles as $cycle) {
            foreach ($employees as $emp) {
                $basicSalary = 20000 + ($emp->id * 1000);
                $daysWorked = 26;
                $basicEarned = $basicSalary;
                $da = $basicEarned * 0.15;
                $hra = $basicEarned * 0.10;
                $grossSalary = $basicEarned + $da + $hra;
                $pf = $basicEarned * 0.12;
                $esi = $basicEarned * 0.0175;
                $deductions = $pf + $esi;
                $netSalary = $grossSalary - $deductions;
                
                DB::table('workforce_payroll_entry')->insert([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'payroll_cycle_id' => $cycle->id,
                    'employee_id' => $emp->id,
                    'total_days_worked' => $daysWorked,
                    'paid_leave_days' => 0,
                    'unpaid_leave_days' => 0,
                    'overtime_hours' => 0,
                    'basic_earned' => round($basicEarned, 2),
                    'da_earned' => round($da, 2),
                    'hra_earned' => round($hra, 2),
                    'other_allowances' => 0,
                    'overtime_wages' => 0,
                    'gross_salary' => round($grossSalary, 2),
                    'pf_employee' => round($pf, 2),
                    'esi_employee' => round($esi, 2),
                    'professional_tax' => 0,
                    'fines' => 0,
                    'advances' => 0,
                    'other_deductions' => 0,
                    'total_deductions' => round($deductions, 2),
                    'net_salary' => round($netSalary, 2),
                    'payment_date' => Carbon::parse($cycle->period_to)->addDays(5),
                    'payment_mode' => 'Bank Transfer',
                    'transaction_reference' => 'TXN/' . $emp->employee_code . '/' . date('Ym', strtotime($cycle->period_from)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info("✓ Created Payroll Entries");
    }
    
    private function createComplianceSections(): void
    {
        $sections = [
            ['name' => 'CLRA Forms', 'code' => 'CLRA'],
            ['name' => 'Labour Welfare', 'code' => 'LABOUR_WELFARE'],
            ['name' => 'Social Security', 'code' => 'SOCIAL_SECURITY'],
            ['name' => 'Factories Act', 'code' => 'FACTORIES'],
            ['name' => 'Shops & Establishment', 'code' => 'SHOPS'],
        ];
        
        foreach ($sections as $section) {
            DB::table('compliance_sections')->insert([
                'section_name' => $section['name'],
                'section_code' => $section['code'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("✓ Created Compliance Sections");
    }
    
    private function createComplianceForms(): void
    {
        $forms = [
            ['code' => 'FORM_B', 'name' => 'Muster Roll', 'section' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_10', 'name' => 'Adult Worker Register', 'section' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_25', 'name' => 'Muster Roll', 'section' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XII', 'name' => 'Register of Workmen', 'section' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_A', 'name' => 'Wage Register', 'section' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_C', 'name' => 'Bonus Register', 'section' => 'Factories', 'frequency' => 'Monthly'],
        ];
        
        $sectionMap = [
            'Factories' => 4,
            'CLRA' => 1,
        ];
        
        foreach ($forms as $form) {
            $sectionId = $sectionMap[$form['section']] ?? 1;
            DB::table('compliance_forms_master')->insert([
                'section_id' => $sectionId,
                'form_code' => $form['code'],
                'form_name' => $form['name'],
                'act_type' => $form['section'],
                'frequency' => $form['frequency'],
                'priority' => 'Medium',
                'auto_generate' => true,
                'upload_only' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("✓ Created Compliance Forms");
    }
}
