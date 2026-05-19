<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BonusSeeder extends Seeder
{
    public function run(): void
    {
        $bonuses = [];
        $bonusId = 1;

        $employees = DB::table('workforce_employee')->get();
        $currentYear = date('Y');

        foreach ($employees as $employee) {
            // Create bonus for current financial year
            $bonusPercentage = rand(8, 16);
            $basicSalary = $employee->basic_salary;
            $bonusAmount = ($basicSalary * $bonusPercentage) / 100;

            $bonuses[] = [
                'id' => $bonusId++,
                'tenant_id' => $employee->tenant_id,
                'branch_id' => $employee->branch_id,
                'employee_id' => $employee->id,
                'financial_year' => ($currentYear - 1) . '-' . $currentYear,
                'bonus_percentage' => $bonusPercentage,
                'bonus_amount' => round($bonusAmount, 2),
                'payment_date' => date('Y-04-30'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('bonus_records')->insert($bonuses);
    }
}
