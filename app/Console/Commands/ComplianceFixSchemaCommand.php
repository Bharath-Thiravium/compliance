<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ComplianceFixSchemaCommand extends Command
{
    protected $signature = 'compliance:fix-schema';
    protected $description = 'Scan form services for invalid database columns and suggest fixes';

    private array $tables = [
        'contractor_master',
        'contract_labour_deployment',
        'workforce_employee',
        'workforce_payroll_entry',
        'workforce_payroll_cycle',
        'incident_documents',
        'bonus_records',
        'workforce_attendance',
    ];

    private array $columnMappings = [
        'contractor_name' => 'company_name',
        'worker_name' => 'workforce_employee.name',
        'basic_salary' => 'wages',
        'date_of_birth' => "''",
        'severity' => "''",
        'action_taken' => "''",
        'bonus_date' => 'payment_date',
        'deployment_date' => 'deployment_start',
    ];

    private array $schemaColumns = [];
    private array $issues = [];

    public function handle(): int
    {
        $this->info('Scanning Compliance Form Services...');
        $this->newLine();

        // Load schema
        $this->loadSchemaColumns();

        // Scan form services
        $formServices = $this->scanFormServices();

        // Analyze queries
        foreach ($formServices as $serviceName => $filePath) {
            $this->analyzeService($serviceName, $filePath);
        }

        // Display results
        $this->displayResults();

        return 0;
    }

    private function loadSchemaColumns(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                $this->schemaColumns[$table] = Schema::getColumnListing($table);
            }
        }
    }

    private function scanFormServices(): array
    {
        $servicesPath = app_path('Services/Compliance/Forms');
        $services = [];

        if (!is_dir($servicesPath)) {
            return $services;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($servicesPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php' && $file->getFilename() !== 'BaseFormService.php') {
                $serviceName = str_replace('Service.php', '', $file->getFilename());
                $services[$serviceName] = $file->getRealPath();
            }
        }

        return $services;
    }

    private function analyzeService(string $serviceName, string $filePath): void
    {
        $content = file_get_contents($filePath);

        // Extract column references from select() and DB::raw()
        $columns = $this->extractColumns($content);

        foreach ($columns as $column) {
            if (!$this->isValidColumn($column)) {
                $suggestion = $this->getSuggestion($column);
                $this->issues[] = [
                    'service' => $serviceName,
                    'invalid_column' => $column,
                    'suggestion' => $suggestion,
                ];
            }
        }
    }

    private function extractColumns(string $content): array
    {
        $columns = [];

        // Pattern 1: ->select(['...', 'table.column', ...])
        if (preg_match_all("/->select\s*\(\s*\[([^\]]+)\]/", $content, $matches)) {
            foreach ($matches[1] as $selectBlock) {
                preg_match_all("/['\"]([a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*)['\"]|['\"]([a-zA-Z_][a-zA-Z0-9_]*)['\"](?!\s*as)/", $selectBlock, $cols);
                foreach ($cols[1] as $col) {
                    if ($col) {
                        $columns[] = $col;
                    }
                }
                foreach ($cols[2] as $col) {
                    if ($col && !in_array($col, ['id', 'tenant_id', 'branch_id'])) {
                        $columns[] = $col;
                    }
                }
            }
        }

        // Pattern 2: DB::raw() references
        if (preg_match_all("/DB::raw\s*\(\s*['\"]([^'\"]*[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*)['\"]\s*\)/", $content, $matches)) {
            foreach ($matches[1] as $raw) {
                preg_match_all("/([a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*)/", $raw, $cols);
                foreach ($cols[1] as $col) {
                    $columns[] = $col;
                }
            }
        }

        return array_unique($columns);
    }

    private function isValidColumn(string $column): bool
    {
        // Handle table.column format
        if (strpos($column, '.') !== false) {
            [$tableAlias, $columnName] = explode('.', $column, 2);
            
            // Check all tables for this column
            foreach ($this->schemaColumns as $table => $cols) {
                if (in_array($columnName, $cols)) {
                    return true;
                }
            }
            return false;
        }

        // Check if column exists in any table
        foreach ($this->schemaColumns as $cols) {
            if (in_array($column, $cols)) {
                return true;
            }
        }

        return false;
    }

    private function getSuggestion(string $column): string
    {
        // Extract column name if it's table.column format
        $columnName = strpos($column, '.') !== false ? explode('.', $column)[1] : $column;

        if (isset($this->columnMappings[$columnName])) {
            return $this->columnMappings[$columnName];
        }

        // Try to find similar columns in schema
        foreach ($this->schemaColumns as $table => $cols) {
            foreach ($cols as $col) {
                if (stripos($col, $columnName) !== false || stripos($columnName, $col) !== false) {
                    return $col;
                }
            }
        }

        return "''";
    }

    private function displayResults(): void
    {
        if (empty($this->issues)) {
            $this->info('No invalid columns found!');
            return;
        }

        $tableData = [];
        foreach ($this->issues as $issue) {
            $tableData[] = [
                $issue['service'],
                $issue['invalid_column'],
                $issue['suggestion'],
            ];
        }

        $this->table(
            ['FORM SERVICE', 'INVALID COLUMN', 'SUGGESTED FIX'],
            $tableData
        );

        $this->newLine();
        $this->info('Scan Complete');
        $this->newLine();
        $this->line('Services Checked: ' . count($this->scanFormServices()));
        $this->line('Issues Found: ' . count($this->issues));
        
        $fixable = count(array_filter($this->issues, fn($i) => $i['suggestion'] !== "''"));
        $manual = count($this->issues) - $fixable;
        
        $this->line('Fixable Automatically: ' . $fixable);
        $this->line('Manual Review Needed: ' . $manual);
    }
}
