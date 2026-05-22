<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Str;

class BladeMappingEngine
{
    protected array $columnMappings = [
        'employee_name' => 'workforce_employee.name',
        'name' => 'workforce_employee.name',
        'father_name' => 'workforce_employee.father_name',
        'designation' => 'workforce_employee.designation',
        'damage_date' => 'workforce_attendance.attendance_date',
        'deduction_amount' => '(workforce_payroll_entry.fines + workforce_payroll_entry.other_deductions)',
        'joining_date' => 'contract_labour_deployment.deployment_start',
        'termination_date' => 'contract_labour_deployment.deployment_end',
        'contractor_name' => 'contractor_master.company_name',
        'contractor_address' => 'contractor_master.company_address',
        'nature_of_work' => 'contract_labour_deployment.nature_of_work',
        'work_location' => 'contract_labour_deployment.work_location',
        'contract_from' => 'contract_labour_deployment.deployment_start',
        'contract_to' => 'contract_labour_deployment.deployment_end',
        'max_workers' => 'contract_labour_deployment.employee_id',
        'showed_cause' => '',
        'witness_name' => '',
        'damage_particulars' => '',
        'instalments' => '',
        'first_month' => '',
        'last_month' => '',
        'remarks' => '',
        'act_or_omission' => '',
        'date_of_offence' => '',
        'heard_by' => '',
        'wage_period' => '',
        'fine_amount' => '',
        'fine_realised' => '',
        'advance_date_amount_1' => '',
        'advance_date_amount_2' => '',
        'purpose' => '',
        'installment_repaid' => '',
        'last_installment_date' => '',
        'signature' => '',
        'sex' => '',
        'overtime_dates' => '',
        'total_overtime' => '',
        'normal_rate' => '',
        'overtime_rate' => '',
        'overtime_earnings' => '',
        'payment_date' => '',
    ];

    public function extractColumns(string $bladeContent): array
    {
        $columns = [];
        
        // Pattern 1: $row['column_name']
        if (preg_match_all("/\\\$row\['([^']+)'\]/", $bladeContent, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }
        
        // Pattern 2: data_get($row, 'column_name')
        if (preg_match_all("/data_get\(\\\$row,\s*['\"]([^'\"]+)['\"]\)/", $bladeContent, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }
        
        // Pattern 3: {{ $row['column_name'] ?? '' }}
        if (preg_match_all("/\{\{\s*\\\$row\['([^']+)'\]\s*\?\?/", $bladeContent, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }

        return array_unique($columns);
    }

    public function getMapping(string $column): string
    {
        return $this->columnMappings[$column] ?? '';
    }

    public function generateRowMapping(array $columns): string
    {
        $mapping = "\$rows[] = [\n";
        
        foreach ($columns as $column) {
            $dbMapping = $this->getMapping($column);
            
            if (empty($dbMapping)) {
                $mapping .= "    '{$column}' => '',\n";
            } else {
                $mapping .= "    '{$column}' => {$dbMapping} ?? '',\n";
            }
        }
        
        $mapping .= "];";
        return $mapping;
    }

    public function getFormCode(string $filename): string
    {
        $name = str_replace('.blade.php', '', $filename);
        $name = str_replace('_', ' ', $name);
        return strtoupper($name);
    }
}
