<?php

namespace App\Console\Commands;

use App\Services\Compliance\SmartSupplementaryTemplateGenerator;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VerifyExcelDropdowns extends Command
{
    protected $signature = 'excel:verify-dropdowns {type=bonus} {tenant_id=1} {branch_id=1}';
    protected $description = 'Verify Excel dropdown implementation';

    public function handle()
    {
        $type = $this->argument('type');
        $tenantId = $this->argument('tenant_id');
        $branchId = $this->argument('branch_id');

        try {
            $this->info("Generating {$type} template for tenant {$tenantId}, branch {$branchId}...");
            
            $generator = new SmartSupplementaryTemplateGenerator();
            $spreadsheet = $generator->generate($type, $tenantId, $branchId);

            // Verify named ranges exist
            $ranges = $spreadsheet->getNamedRanges();
            $this->line('');
            
            if (empty($ranges)) {
                $this->error('✗ NO NAMED RANGES FOUND');
                $this->line('');
                $this->comment('Debugging info:');
                $this->line('- Check if EmployeeMaster sheet was created');
                $this->line('- Check if createNamedRanges() was called');
                $this->line('- Check logs: tail -f storage/logs/laravel.log | grep "Named range"');
                return 1;
            }

            $this->info('✓ Named Ranges Found:');
            foreach ($ranges as $range) {
                $this->line("  ✓ {$range->getName()} = {$range->getRange()}");
            }

            // Verify validation on dropdown cells
            $formSheet = $spreadsheet->getSheetByName('Form');
            $this->line('');
            $this->info('✓ Validation on Form Sheet:');

            $validationFound = false;
            for ($row = 2; $row <= 5; $row++) {
                $cell = $formSheet->getCell("A{$row}");
                $validation = $cell->getDataValidation();
                
                if ($validation && $validation->getFormula1()) {
                    $this->line("  ✓ Cell A{$row}: {$validation->getFormula1()}");
                    if (strpos($validation->getFormula1(), 'EmployeeCodes') !== false) {
                        $validationFound = true;
                    }
                }
            }

            if (!$validationFound) {
                $this->error('  ✗ Validation formulas not using named ranges');
                return 1;
            }

            // Check if EmployeeMaster exists and is hidden
            $this->line('');
            $this->info('✓ Sheet Configuration:');
            
            $masterSheet = null;
            try {
                $masterSheet = $spreadsheet->getSheetByName('EmployeeMaster');
                $isHidden = $masterSheet->getSheetState() === \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN;
                $this->line('  ✓ EmployeeMaster exists: ' . ($isHidden ? 'Hidden' : 'Visible'));
                $this->line("  ✓ Employee count: " . ($masterSheet->getHighestRow() - 1));
            } catch (\Exception $e) {
                $this->error('  ✗ EmployeeMaster sheet not found');
                return 1;
            }

            // Save test file
            $testFile = storage_path("app/test_dropdown_{$type}_" . now()->timestamp . '.xlsx');
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($testFile);

            $this->line('');
            $this->info('✓ Test file saved: ' . $testFile);
            $this->info('');
            $this->comment('VERIFICATION PASSED - All dropdowns should work in Excel 365');
            $this->comment('');
            $this->comment('Next Steps:');
            $this->comment('1. Download and open: ' . $testFile);
            $this->comment('2. Go to Formulas → Name Manager');
            $this->comment('3. Verify ranges exist: EmployeeCodes, EmployeeNames, FatherNames');
            $this->comment('4. Click cell A2 and verify dropdown arrow appears');
            $this->comment('5. Select employee and verify auto-fill works (if data exists)');
            $this->comment('');

            return 0;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
