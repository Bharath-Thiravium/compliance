<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaIntegrityService
{
    private array $expectedSchema = [
        'tenants' => [
            'establishment_name' => ['type' => 'string', 'nullable' => true],
            'factory_license_no' => ['type' => 'string', 'nullable' => true],
            'pf_code' => ['type' => 'string', 'nullable' => true],
            'esi_code' => ['type' => 'string', 'nullable' => true],
            'labour_office_address' => ['type' => 'string', 'nullable' => true],
        ],
        'branches' => [
            'unit_name' => ['type' => 'string', 'nullable' => true],
            'address' => ['type' => 'text', 'nullable' => true],
        ],
        'workforce_employee' => [
            'basic_salary' => ['type' => 'decimal', 'nullable' => true],
            'status' => ['type' => 'string', 'nullable' => true],
        ],
        'workforce_payroll_entry' => [
            'basic_earned' => ['type' => 'decimal', 'nullable' => true],
            'da_earned' => ['type' => 'decimal', 'nullable' => true],
            'hra_earned' => ['type' => 'decimal', 'nullable' => true],
            'overtime_hours' => ['type' => 'decimal', 'nullable' => true],
            'overtime_wages' => ['type' => 'decimal', 'nullable' => true],
            'total_days_worked' => ['type' => 'integer', 'nullable' => true],
        ],
        'workforce_attendance' => [
            'attendance_date' => ['type' => 'date', 'nullable' => false],
            'status' => ['type' => 'string', 'nullable' => false],
        ],
    ];

    public function audit(): array
    {
        $mismatches = [];

        foreach ($this->expectedSchema as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $mismatches[] = [
                    'type' => 'missing_table',
                    'table' => $table,
                    'severity' => 'critical',
                ];
                continue;
            }

            foreach ($columns as $column => $spec) {
                if (!Schema::hasColumn($table, $column)) {
                    $mismatches[] = [
                        'type' => 'missing_column',
                        'table' => $table,
                        'column' => $column,
                        'spec' => $spec,
                        'severity' => 'high',
                    ];
                }
            }
        }

        return $mismatches;
    }

    public function generateRepairPlan(array $mismatches): array
    {
        $plan = [];

        foreach ($mismatches as $mismatch) {
            if ($mismatch['type'] === 'missing_column') {
                $plan[] = [
                    'action' => 'add_column',
                    'table' => $mismatch['table'],
                    'column' => $mismatch['column'],
                    'type' => $mismatch['spec']['type'],
                    'nullable' => $mismatch['spec']['nullable'],
                ];
            }
        }

        return $plan;
    }

    public function executeRepair(array $plan): void
    {
        foreach ($plan as $action) {
            if ($action['action'] === 'add_column') {
                Schema::table($action['table'], function ($table) use ($action) {
                    $column = null;
                    
                    switch ($action['type']) {
                        case 'string':
                            $column = $table->string($action['column']);
                            break;
                        case 'text':
                            $column = $table->text($action['column']);
                            break;
                        case 'decimal':
                            $column = $table->decimal($action['column'], 10, 2);
                            break;
                        case 'integer':
                            $column = $table->integer($action['column']);
                            break;
                        case 'date':
                            $column = $table->date($action['column']);
                            break;
                    }

                    if ($column && $action['nullable']) {
                        $column->nullable();
                    }
                });
            }
        }
    }
}
