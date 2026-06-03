<?php

namespace App\Console\Commands;

use App\Services\Compliance\SmartSupplementaryTemplateGenerator;
use Illuminate\Console\Command;

class DebugAutoFill extends Command
{
    protected $signature = 'excel:debug-autofill {type=bonus} {tenant_id=1} {branch_id=1}';
    protected $description = 'Debug auto-fill formulas';

    public function handle()
    {
        $type = $this->argument('type');
        $tenantId = $this->argument('tenant_id');
        $branchId = $this->argument('branch_id');

        $generator = new SmartSupplementaryTemplateGenerator();
        $spreadsheet = $generator->generate($type, $tenantId, $branchId);
        $sheet = $spreadsheet->getSheetByName('Form');

        $this->info("Checking auto-fill formulas in Form sheet:");
        $this->line('');

        $testRows = [2, 5, 10, 50, 100, 200, 500];
        foreach ($testRows as $row) {
            $cellB = $sheet->getCell("B{$row}");
            $cellC = $sheet->getCell("C{$row}");
            $cellD = $sheet->getCell("D{$row}");

            $this->info("Row {$row}:");
            $this->line("  B{$row} (employee_name): " . ($cellB->getValue() ?? 'EMPTY'));
            $this->line("  C{$row} (father_name): " . ($cellC->getValue() ?? 'EMPTY'));
            $this->line("  D{$row} (department): " . ($cellD->getValue() ?? 'EMPTY'));
        }

        return 0;
    }
}
