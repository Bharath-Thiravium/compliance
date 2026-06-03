<?php

namespace App\Services\Compliance;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SupplementaryXlsxUploadService
{
    public function upload(UploadedFile $file, string $type, int $tenantId, int $branchId): array
    {
        $rows = $this->parseXlsx($file, $type);

        $result = DB::transaction(function () use ($rows, $type, $tenantId, $branchId) {
            return match ($type) {
                'bonus'           => $this->insertBonus($rows, $tenantId, $branchId),
                'fines'           => $this->insertFines($rows, $tenantId, $branchId),
                'advances'        => $this->insertAdvances($rows, $tenantId, $branchId),
                'deductions'      => $this->insertDeductions($rows, $tenantId, $branchId),
                'incidents'       => $this->insertIncidents($rows, $tenantId, $branchId),
                'hazard_register' => $this->insertHazardRegister($rows, $tenantId, $branchId),
                'contractors'     => $this->insertContractors($rows, $tenantId, $branchId),
                default           => throw new \InvalidArgumentException("Unknown dataset type: {$type}"),
            };
        });

        Log::info('Supplementary XLSX upload complete', [
            'type'     => $type,
            'tenant'   => $tenantId,
            'branch'   => $branchId,
            'inserted' => $result['inserted'],
            'skipped'  => $result['skipped'] ?? 0,
        ]);

        return $result;
    }

    private function parseXlsx(UploadedFile $file, string $type): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheetByName('Form') ?? $spreadsheet->getActiveSheet();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Cannot read XLSX file: " . $e->getMessage());
        }

        // Get headers from row 1
        $headers = [];
        $colIndex = 1;
        while ($value = $sheet->getCellByColumnAndRow($colIndex, 1)->getValue()) {
            $headers[$colIndex] = strtolower(trim($value));
            $colIndex++;
            if ($colIndex > 50) break; // Safety limit
        }

        if (empty($headers)) {
            throw new \InvalidArgumentException("No headers found in XLSX file");
        }

        // Parse rows starting from row 2
        $rows = [];
        $rowIndex = 2;
        while ($rowIndex <= $sheet->getHighestRow()) {
            $row = [];
            $hasData = false;

            foreach ($headers as $colIndex => $header) {
                $value = $sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
                if ($value !== null && $value !== '') {
                    $hasData = true;
                }
                $row[$header] = $value;
            }

            // Skip empty rows
            if ($hasData) {
                $rows[] = $row;
            }

            $rowIndex++;
        }

        if (empty($rows)) {
            throw new \InvalidArgumentException("No valid data rows found in XLSX file");
        }

        Log::debug("XLSX ({$type}) parsed", ['rows' => count($rows), 'headers' => $headers]);

        return $rows;
    }

    private function resolveEmployeeId(string $code, int $tenantId, int $branchId): int
    {
        $id = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('employee_code', $code)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$id) {
            throw new \RuntimeException("Employee not found: {$code}");
        }

        return (int) $id;
    }

    private function insertBonus(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $code = trim($row['employee_code'] ?? '');
            if ($code === '') { continue; }
            try {
                $empId = $this->resolveEmployeeId($code, $tenantId, $branchId);
                $fy    = trim($row['financial_year'] ?? '');
                DB::table('bonus_records')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'employee_id' => $empId, 'financial_year' => $fy],
                    [
                        'bonus_percentage' => CsvNormalizer::normalizeFloat($row['bonus_percentage'] ?? null),
                        'bonus_amount'     => CsvNormalizer::normalizeFloat($row['bonus_amount'] ?? null),
                        'payment_date'     => CsvNormalizer::normalizeDate($row['payment_date'] ?? null),
                        'updated_at'       => now(),
                        'created_at'       => now(),
                    ]
                );
                $count++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . " ({$code}): " . $e->getMessage();
                Log::warning("Bonus row skipped", ['row' => $i + 2, 'code' => $code, 'error' => $e->getMessage()]);
            }
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'bonus'];
    }

    private function insertFines(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $code = trim($row['employee_code'] ?? '');
            if ($code === '') { continue; }
            try {
                $empId    = $this->resolveEmployeeId($code, $tenantId, $branchId);
                $fineDate = CsvNormalizer::normalizeDate($row['fine_date'] ?? null);
                if (!$fineDate) {
                    throw new \InvalidArgumentException("Invalid fine_date");
                }
                DB::table('workforce_fines')->insert([
                    'tenant_id'    => $tenantId,
                    'branch_id'    => $branchId,
                    'employee_id'  => $empId,
                    'fine_date'    => $fineDate,
                    'reason'       => $row['fine_reason'] ?? null,
                    'amount'       => CsvNormalizer::normalizeFloat($row['amount'] ?? null),
                    'showed_cause' => CsvNormalizer::normalizeBool($row['showed_cause'] ?? null),
                    'heard_by'     => $row['heard_by']     ?? null,
                    'witness_name' => $row['witness_name'] ?? null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . " ({$code}): " . $e->getMessage();
                Log::warning("Fines row skipped", ['row' => $i + 2, 'code' => $code, 'error' => $e->getMessage()]);
            }
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'fines'];
    }

    private function insertAdvances(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $code = trim($row['employee_code'] ?? '');
            if ($code === '') { continue; }
            try {
                $empId       = $this->resolveEmployeeId($code, $tenantId, $branchId);
                $advanceDate = CsvNormalizer::normalizeDate($row['advance_date'] ?? null);
                if (!$advanceDate) {
                    throw new \InvalidArgumentException("Invalid advance_date");
                }
                DB::table('workforce_advances')->insert([
                    'tenant_id'           => $tenantId,
                    'branch_id'           => $branchId,
                    'employee_id'         => $empId,
                    'advance_date'        => $advanceDate,
                    'amount'              => CsvNormalizer::normalizeFloat($row['advance_amount'] ?? null),
                    'purpose'             => $row['purpose'] ?? null,
                    'num_instalments'     => CsvNormalizer::normalizeInt($row['installment_count'] ?? null) ?: null,
                    'monthly_installment' => CsvNormalizer::normalizeFloat($row['monthly_installment'] ?? null),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . " ({$code}): " . $e->getMessage();
                Log::warning("Advances row skipped", ['row' => $i + 2, 'code' => $code, 'error' => $e->getMessage()]);
            }
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'advances'];
    }

    private function insertDeductions(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $code = trim($row['employee_code'] ?? '');
            if ($code === '') { continue; }
            try {
                $empId         = $this->resolveEmployeeId($code, $tenantId, $branchId);
                $deductionDate = CsvNormalizer::normalizeDate($row['deduction_date'] ?? null);
                if (!$deductionDate) {
                    throw new \InvalidArgumentException("Invalid deduction_date");
                }
                DB::table('workforce_deductions')->insert([
                    'tenant_id'      => $tenantId,
                    'branch_id'      => $branchId,
                    'employee_id'    => $empId,
                    'deduction_date' => $deductionDate,
                    'deduction_type' => $row['deduction_type'] ?? null,
                    'particulars'    => $row['damage_particulars'] ?? null,
                    'amount'         => CsvNormalizer::normalizeFloat($row['amount'] ?? null),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . " ({$code}): " . $e->getMessage();
                Log::warning("Deductions row skipped", ['row' => $i + 2, 'code' => $code, 'error' => $e->getMessage()]);
            }
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'deductions'];
    }

    private function insertIncidents(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $incidentDate = CsvNormalizer::normalizeDate($row['incident_date'] ?? null);
            if (!$incidentDate) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . ": invalid incident_date";
                Log::warning("Incidents row skipped: invalid date", ['row' => $i + 2]);
                continue;
            }

            $empId = null;
            if (!empty($row['employee_code'])) {
                try {
                    $empId = $this->resolveEmployeeId(trim($row['employee_code']), $tenantId, $branchId);
                } catch (\RuntimeException) {
                    // Incidents can exist without a linked employee
                }
            }

            DB::table('incidents')->insert([
                'tenant_id'          => $tenantId,
                'branch_id'          => $branchId,
                'employee_id'        => $empId,
                'incident_date'      => $incidentDate,
                'location'           => $row['location']          ?? null,
                'injury_type'        => $row['injury_type']       ?? null,
                'severity'           => $row['severity']          ?? 'low',
                'cause'              => $row['root_cause']        ?? null,
                'root_cause'         => $row['root_cause']        ?? null,
                'corrective_action'  => $row['corrective_action'] ?? null,
                'preventive_action'  => $row['preventive_action'] ?? null,
                'medical_leave_days' => CsvNormalizer::normalizeInt($row['medical_leave_days'] ?? null),
                'description'        => $row['description']       ?? $row['injury_type'] ?? null,
                'status'             => 'open',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $count++;
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'incidents'];
    }

    private function insertHazardRegister(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $hazardType = trim($row['hazard_type'] ?? '');
            if ($hazardType === '') { continue; }
            try {
                DB::table('hazard_register')->insert([
                    'tenant_id'         => $tenantId,
                    'branch_id'         => $branchId,
                    'hazard_date'       => now()->toDateString(),
                    'hazard_type'       => $hazardType,
                    'description'       => $row['control_measure']   ?? '',
                    'location'          => $row['location']          ?? '',
                    'severity'          => $this->mapRiskToSeverity($row['risk_rating'] ?? null),
                    'risk_rating'       => $row['risk_rating']       ?? null,
                    'control_measure'   => $row['control_measure']   ?? null,
                    'corrective_action' => $row['corrective_action'] ?? null,
                    'reported_by'       => $row['reported_by']       ?? null,
                    'status'            => 'open',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
                Log::warning("Hazard register row skipped", ['row' => $i + 2, 'error' => $e->getMessage()]);
            }
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'hazard_register'];
    }

    private function mapRiskToSeverity(?string $risk): string
    {
        if (empty($risk)) return 'medium';
        $v = strtolower(trim($risk));
        return match (true) {
            in_array($v, ['critical', 'very high', 'extreme']) => 'critical',
            in_array($v, ['high', 'h'])                        => 'high',
            in_array($v, ['low', 'l', 'minimal'])              => 'low',
            default                                            => 'medium',
        };
    }

    private function insertContractors(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $name    = trim($row['contractor_name'] ?? '');
            $license = trim($row['license_number']  ?? '');
            if ($name === '') { continue; }

            try {
                DB::table('contractor_master')->updateOrInsert(
                    [
                        'tenant_id'       => $tenantId,
                        'branch_id'       => $branchId,
                        'license_number'  => $license ?: null,
                        'contractor_name' => $name,
                    ],
                    [
                        'company_name'    => $name,
                        'company_type'    => 'contractor',
                        'contractor_code' => $row['contractor_code'] ?? null,
                        'address'         => $row['address']         ?? null,
                        'company_address' => $row['address']         ?? null,
                        'contact_person'  => $row['contact_person']  ?? null,
                        'contact_number'  => $row['mobile']          ?? null,
                        'phone'           => $row['mobile']          ?? null,
                        'email'           => $row['email']           ?? null,
                        'valid_from'      => CsvNormalizer::normalizeDate($row['valid_from'] ?? null) ?? now()->toDateString(),
                        'valid_to'        => CsvNormalizer::normalizeDate($row['valid_to']   ?? null) ?? now()->addYear()->toDateString(),
                        'license_no'      => $license ?: null,
                        'license_expiry'  => CsvNormalizer::normalizeDate($row['valid_to']   ?? null),
                        'max_worker_limit'=> CsvNormalizer::normalizeInt($row['max_workers'] ?? null) ?: null,
                        'status'          => 'active',
                        'updated_at'      => now(),
                        'created_at'      => now(),
                    ]
                );

                if ($license !== '') {
                    DB::table('contractors')->updateOrInsert(
                        ['tenant_id' => $tenantId, 'license_number' => $license],
                        [
                            'contractor_name'  => $name,
                            'valid_from'       => CsvNormalizer::normalizeDate($row['valid_from'] ?? null) ?? now()->toDateString(),
                            'valid_to'         => CsvNormalizer::normalizeDate($row['valid_to']   ?? null) ?? now()->addYear()->toDateString(),
                            'max_worker_limit' => CsvNormalizer::normalizeInt($row['max_workers'] ?? null) ?: null,
                            'pf_code'          => $row['pf_code']  ?? null,
                            'esi_code'         => $row['esi_code'] ?? null,
                            'updated_at'       => now(),
                            'created_at'       => now(),
                        ]
                    );
                }
                $count++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row " . ($i + 2) . " ({$name}): " . $e->getMessage();
                Log::warning("Contractors row skipped", ['row' => $i + 2, 'name' => $name, 'error' => $e->getMessage()]);
            }
        }
        return ['inserted' => $count, 'skipped' => $skipped, 'errors' => $errors, 'type' => 'contractors'];
    }
}
