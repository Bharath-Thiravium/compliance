<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class SmartSupplementaryTemplateGenerator
{
    private const TEMPLATES = [
        'bonus' => [
            'headers' => ['employee_code', 'employee_name', 'father_name', 'department', 'designation', 'financial_year', 'bonus_percentage', 'bonus_amount', 'payment_date', 'remarks'],
            'locked_fields' => ['employee_name', 'father_name', 'department', 'designation'],
            'editable_fields' => ['employee_code', 'financial_year', 'bonus_percentage', 'bonus_amount', 'payment_date', 'remarks'],
        ],
        'fines' => [
            'headers' => ['employee_code', 'employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number', 'fine_date', 'fine_reason', 'amount', 'showed_cause', 'heard_by', 'witness_name', 'remarks'],
            'locked_fields' => ['employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number'],
            'editable_fields' => ['employee_code', 'fine_date', 'fine_reason', 'amount', 'showed_cause', 'heard_by', 'witness_name', 'remarks'],
        ],
        'advances' => [
            'headers' => ['employee_code', 'employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number', 'advance_date', 'advance_amount', 'purpose', 'installment_count', 'monthly_installment', 'remarks'],
            'locked_fields' => ['employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number'],
            'editable_fields' => ['employee_code', 'advance_date', 'advance_amount', 'purpose', 'installment_count', 'monthly_installment', 'remarks'],
        ],
        'deductions' => [
            'headers' => ['employee_code', 'employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number', 'deduction_date', 'deduction_type', 'damage_particulars', 'amount', 'remarks'],
            'locked_fields' => ['employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number'],
            'editable_fields' => ['employee_code', 'deduction_date', 'deduction_type', 'damage_particulars', 'amount', 'remarks'],
        ],
        'incidents' => [
            'headers' => ['incident_date', 'employee_code', 'employee_name', 'father_name', 'department', 'designation', 'location', 'injury_type', 'severity', 'root_cause', 'corrective_action', 'preventive_action', 'medical_leave_days', 'remarks'],
            'locked_fields' => ['employee_name', 'father_name', 'department', 'designation'],
            'editable_fields' => ['incident_date', 'employee_code', 'location', 'injury_type', 'severity', 'root_cause', 'corrective_action', 'preventive_action', 'medical_leave_days', 'remarks'],
        ],
        'hazard_register' => [
            'headers' => ['hazard_date', 'hazard_type', 'location', 'risk_rating', 'control_measure', 'corrective_action', 'reported_by', 'remarks'],
            'locked_fields' => [],
            'editable_fields' => ['hazard_date', 'hazard_type', 'location', 'risk_rating', 'control_measure', 'corrective_action', 'reported_by', 'remarks'],
        ],
        'contractors' => [
            'headers' => ['contractor_code', 'contractor_name', 'license_number', 'valid_from', 'valid_to', 'nature_of_work', 'contact_person', 'mobile', 'email', 'address', 'max_workers', 'pf_code', 'esi_code', 'remarks'],
            'locked_fields' => [],
            'editable_fields' => ['contractor_code', 'contractor_name', 'license_number', 'valid_from', 'valid_to', 'nature_of_work', 'contact_person', 'mobile', 'email', 'address', 'max_workers', 'pf_code', 'esi_code', 'remarks'],
        ],
    ];

    public function generate(string $type, int $tenantId, int $branchId): Spreadsheet
    {
        if (!isset(self::TEMPLATES[$type])) {
            throw new \InvalidArgumentException("Unknown template type: {$type}");
        }

        $startedAt = microtime(true);

        try {
            $spreadsheet = $this->buildWorkbook($type, $tenantId, $branchId);

            Log::info('Smart template workbook generated', [
                'type' => $type,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'execution_time_seconds' => round(microtime(true) - $startedAt, 3),
            ]);

            return $spreadsheet;
        } catch (\Throwable $e) {
            Log::critical('Smart template generation failed', [
                'type' => $type,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->generateFallbackTemplate($type);
        }
    }

    public static function headersFor(string $type): array
    {
        if (!isset(self::TEMPLATES[$type])) {
            throw new \InvalidArgumentException("Unknown template type: {$type}");
        }

        return self::TEMPLATES[$type]['headers'];
    }

    public function generateFallbackTemplate(string $type): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Form');

        foreach (self::headersFor($type) as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(18);
        }

        return $spreadsheet;
    }

    private function buildWorkbook(string $type, int $tenantId, int $branchId): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $formSheet = $spreadsheet->createSheet();
        $formSheet->setTitle('Form');
        
        if ($this->hasEmployeeFields($type)) {
            $masterSheet = $spreadsheet->createSheet();
            $masterSheet->setTitle('EmployeeMaster');
            $masterSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            $this->populateEmployeeMasterSheet($masterSheet, $tenantId, $branchId);
            $this->createNamedRanges($spreadsheet, $masterSheet);
            $this->addRecalculationTrigger($formSheet, $type);
            $this->addDropdownValidations($formSheet, $type);
            $this->addAutoFillFormulas($formSheet, $type);
        }

        $this->populateFormSheet($formSheet, $type);
        $this->applySheetProtection($formSheet, $type);

        return $spreadsheet;
    }

    private function populateFormSheet(Worksheet $sheet, string $type): void
    {
        $config = self::TEMPLATES[$type];
        $headers = $config['headers'];

        foreach ($headers as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $cell->getStyle()->getFill()->getStartColor()->setARGB('FFD3D3D3');
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(18);
        }

        $sheet->getProtection()->setSheet(false);
    }

    private function createNamedRanges(Spreadsheet $spreadsheet, Worksheet $masterSheet): void
    {
        $lastRow = $masterSheet->getHighestRow();
        $endRow = min(200, max(2, $lastRow));

        $namedRanges = [
            'EmployeeCodes' => "EmployeeMaster!\$A\$2:\$A\${$endRow}",
            'EmployeeNames' => "EmployeeMaster!\$B\$2:\$B\${$endRow}",
            'FatherNames' => "EmployeeMaster!\$C\$2:\$C\${$endRow}",
        ];

        foreach ($namedRanges as $name => $range) {
            try {
                try {
                    $spreadsheet->removeNamedRange($name);
                } catch (\Exception $e) {
                }
                
                $namedRange = new NamedRange($name, $masterSheet, $range);
                $spreadsheet->addNamedRange($namedRange);
            } catch (\Exception $e) {
                Log::warning("Failed to create named range {$name}: " . $e->getMessage());
            }
        }
    }

    private function addDropdownValidations(Worksheet $formSheet, string $type): void
    {
        $headers = self::TEMPLATES[$type]['headers'];
        
        $employeeCodeCol = array_search('employee_code', $headers);
        if ($employeeCodeCol !== false) {
            $this->addDropdownToColumn($formSheet, $employeeCodeCol, 'EmployeeCodes');
        }
        
        $employeeNameCol = array_search('employee_name', $headers);
        if ($employeeNameCol !== false) {
            $this->addDropdownToColumn($formSheet, $employeeNameCol, 'EmployeeNames');
        }
        
        $fatherNameCol = array_search('father_name', $headers);
        if ($fatherNameCol !== false) {
            $this->addDropdownToColumn($formSheet, $fatherNameCol, 'FatherNames');
        }
    }

    private function addDropdownToColumn(Worksheet $sheet, int $colIndex, string $namedRange): void
    {
        try {
            $colLetter = chr(65 + $colIndex);
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setFormula1("={$namedRange}");
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setSqref("{$colLetter}2:{$colLetter}200");
            $sheet->setDataValidation("{$colLetter}2", $validation);
        } catch (\Throwable $e) {
            Log::warning("Failed to add dropdown to column {$colIndex}: {$e->getMessage()}");
        }
    }

    private function addAutoFillFormulas(Worksheet $formSheet, string $type): void
    {
        $headers = self::TEMPLATES[$type]['headers'];
        
        $employeeCodeCol = array_search('employee_code', $headers);
        $employeeNameCol = array_search('employee_name', $headers);
        $fatherNameCol = array_search('father_name', $headers);

        if ($employeeCodeCol === false && $employeeNameCol === false && $fatherNameCol === false) {
            return;
        }

        $nameCol = $employeeNameCol !== false ? chr(65 + $employeeNameCol) : null;
        $fatherCol = $fatherNameCol !== false ? chr(65 + $fatherNameCol) : null;
        $codeCol = $employeeCodeCol !== false ? chr(65 + $employeeCodeCol) : null;

        for ($row = 2; $row <= 200; $row++) {
            // Employee Name formula - lookup by code, references TODAY() to force recalc
            if ($nameCol && $codeCol) {
                $formula = "=IF(TODAY()>0,IFERROR(XLOOKUP({$codeCol}{$row},EmployeeMaster!\$A:A,EmployeeMaster!\$B:B,\"\"),\"\"),\"\")";
                $formSheet->getCell("{$nameCol}{$row}")->setValueExplicit($formula, DataType::TYPE_FORMULA);
            }

            // Father Name formula - lookup by code, references TODAY() to force recalc
            if ($fatherCol && $codeCol) {
                $formula = "=IF(TODAY()>0,IFERROR(XLOOKUP({$codeCol}{$row},EmployeeMaster!\$A:A,EmployeeMaster!\$C:C,\"\"),\"\"),\"\")";
                $formSheet->getCell("{$fatherCol}{$row}")->setValueExplicit($formula, DataType::TYPE_FORMULA);
            }

            // Department, designation, PF, ESI - lookup by code, references TODAY() to force recalc
            foreach ($this->employeeDetailReturnColumns() as $fieldName => $returnColumn) {
                $templateCol = array_search($fieldName, $headers);

                if ($templateCol === false || !$codeCol) {
                    continue;
                }

                $colLetter = chr(65 + $templateCol);
                $formula = "=IF(TODAY()>0,IFERROR(XLOOKUP({$codeCol}{$row},EmployeeMaster!\$A:A,EmployeeMaster!\${$returnColumn}:{$returnColumn},\"\"),\"\"),\"\")";
                $formSheet->getCell("{$colLetter}{$row}")->setValueExplicit($formula, DataType::TYPE_FORMULA);
            }
        }
    }

    private function addRecalculationTrigger(Worksheet $formSheet, string $type): void
    {
        // Hidden column with TODAY() - recalculates every time Excel opens or any change
        $formSheet->getColumnDimension('AA')->setVisible(false);
        for ($row = 2; $row <= 200; $row++) {
            $formSheet->getCell("AA{$row}")->setValueExplicit('=TODAY()', DataType::TYPE_FORMULA);
        }
    }

    private function employeeDetailReturnColumns(): array
    {
        return [
            'department' => 'D',
            'designation' => 'E',
            'pf_number' => 'F',
            'esi_number' => 'G',
        ];
    }

    private function applySheetProtection(Worksheet $sheet, string $type): void
    {
        $headers = self::TEMPLATES[$type]['headers'];
        $editableFields = self::TEMPLATES[$type]['editable_fields'];

        foreach ($editableFields as $fieldName) {
            $col = array_search($fieldName, $headers);

            if ($col === false) {
                continue;
            }

            $colLetter = chr(65 + $col);
            $sheet->getStyle("{$colLetter}2:{$colLetter}200")
                ->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        $sheet->getProtection()->setSheet(true);
    }

    private function populateEmployeeMasterSheet(Worksheet $sheet, int $tenantId, int $branchId): void
    {
        $headers = ['employee_code', 'employee_name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number'];
        foreach ($headers as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(18);
        }

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->select('employee_code', 'name', 'father_name', 'department', 'designation', 'pf_number', 'esi_number')
            ->orderBy('employee_code')
            ->limit(200)
            ->get();

        $row = 2;
        foreach ($employees as $emp) {
            $sheet->setCellValueByColumnAndRow(1, $row, $emp->employee_code);
            $sheet->setCellValueByColumnAndRow(2, $row, $emp->name);
            $sheet->setCellValueByColumnAndRow(3, $row, $emp->father_name);
            $sheet->setCellValueByColumnAndRow(4, $row, $emp->department);
            $sheet->setCellValueByColumnAndRow(5, $row, $emp->designation);
            $sheet->setCellValueByColumnAndRow(6, $row, $emp->pf_number);
            $sheet->setCellValueByColumnAndRow(7, $row, $emp->esi_number);
            $row++;
        }
    }

    private function hasEmployeeFields(string $type): bool
    {
        return in_array($type, ['bonus', 'fines', 'advances', 'deductions', 'incidents']);
    }
}
