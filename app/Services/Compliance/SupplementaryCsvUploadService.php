<?php

namespace App\Services\Compliance;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles CSV uploads for supplementary compliance datasets:
 * bonus | fines | advances | deductions | incidents | hazard_register
 */
class SupplementaryCsvUploadService
{
    public function upload(UploadedFile $file, string $type, int $tenantId, int $branchId): array
    {
        $rows = $this->parseCsv($file, $type);

        return DB::transaction(function () use ($rows, $type, $tenantId, $branchId) {
            return match ($type) {
                'bonus'          => $this->insertBonus($rows, $tenantId, $branchId),
                'fines'          => $this->insertFines($rows, $tenantId, $branchId),
                'advances'       => $this->insertAdvances($rows, $tenantId, $branchId),
                'deductions'     => $this->insertDeductions($rows, $tenantId, $branchId),
                'incidents'      => $this->insertIncidents($rows, $tenantId, $branchId),
                'hazard_register'=> $this->insertHazardRegister($rows, $tenantId, $branchId),
                'contractors'    => $this->insertContractors($rows, $tenantId, $branchId),
                default          => throw new \InvalidArgumentException("Unknown dataset type: {$type}"),
            };
        });
    }

    // ── CSV Parsing ───────────────────────────────────────────────────────────

    private function parseCsv(UploadedFile $file, string $type): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new \InvalidArgumentException("Cannot open uploaded file.");
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \InvalidArgumentException("CSV ({$type}): file is empty.");
        }

        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rawHeaders = fgetcsv($handle, 4096, $delimiter);
        if (! $rawHeaders) {
            fclose($handle);
            throw new \InvalidArgumentException("CSV ({$type}): could not read header row.");
        }
        $rawHeaders[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeaders[0]);

        $skipped       = [];
        $headerMapping = CsvColumnMapper::mapHeaders($rawHeaders, $type, $skipped);
        $required      = CsvColumnMapper::requiredFields($type);
        $missing       = array_diff($required, array_keys($headerMapping));

        if (! empty($missing)) {
            fclose($handle);
            throw new \InvalidArgumentException(
                "CSV ({$type}): missing required columns: " . implode(', ', $missing)
            );
        }

        if (! empty($skipped)) {
            Log::debug("CSV ({$type}): unrecognised columns skipped", ['skipped' => $skipped]);
        }

        $rows     = [];
        $colCount = count($rawHeaders);

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if ($data === [null] || implode('', $data) === '') continue;
            if (count($data) < $colCount) $data = array_pad($data, $colCount, '');
            elseif (count($data) > $colCount) $data = array_slice($data, 0, $colCount);

            $row = CsvColumnMapper::extractRow($data, $headerMapping);
            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \InvalidArgumentException("CSV ({$type}): no valid data rows found.");
        }

        return $rows;
    }

    // ── Employee resolver ─────────────────────────────────────────────────────

    private function resolveEmployeeId(string $code, int $tenantId, int $branchId): int
    {
        $id = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('employee_code', $code)
            ->whereNull('deleted_at')
            ->value('id');

        if (! $id) {
            throw new \RuntimeException("Employee not found: {$code}. Upload employees.csv first.");
        }

        return (int) $id;
    }

    // ── Bonus ─────────────────────────────────────────────────────────────────

    private function insertBonus(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $empId = $this->resolveEmployeeId($row['employee_code'], $tenantId, $branchId);
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
        }
        return ['inserted' => $count, 'type' => 'bonus'];
    }

    // ── Fines ─────────────────────────────────────────────────────────────────

    private function insertFines(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $empId    = $this->resolveEmployeeId($row['employee_code'], $tenantId, $branchId);
            $fineDate = CsvNormalizer::normalizeDate($row['fine_date'] ?? null);

            if (! $fineDate) {
                throw new \InvalidArgumentException("Invalid fine_date for employee: {$row['employee_code']}");
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
        }
        return ['inserted' => $count, 'type' => 'fines'];
    }

    // ── Advances ──────────────────────────────────────────────────────────────

    private function insertAdvances(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $empId       = $this->resolveEmployeeId($row['employee_code'], $tenantId, $branchId);
            $advanceDate = CsvNormalizer::normalizeDate($row['advance_date'] ?? null);

            if (! $advanceDate) {
                throw new \InvalidArgumentException("Invalid advance_date for employee: {$row['employee_code']}");
            }

            $installments = CsvNormalizer::normalizeInt($row['installment_count'] ?? null);
            $monthly      = CsvNormalizer::normalizeFloat($row['monthly_installment'] ?? null);

            DB::table('workforce_advances')->insert([
                'tenant_id'           => $tenantId,
                'branch_id'           => $branchId,
                'employee_id'         => $empId,
                'advance_date'        => $advanceDate,
                'amount'              => CsvNormalizer::normalizeFloat($row['advance_amount'] ?? null),
                'purpose'             => $row['purpose'] ?? null,
                'num_instalments'     => $installments ?: null,
                'monthly_installment' => $monthly,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
            $count++;
        }
        return ['inserted' => $count, 'type' => 'advances'];
    }

    // ── Deductions ────────────────────────────────────────────────────────────

    private function insertDeductions(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $empId         = $this->resolveEmployeeId($row['employee_code'], $tenantId, $branchId);
            $deductionDate = CsvNormalizer::normalizeDate($row['deduction_date'] ?? null);

            if (! $deductionDate) {
                throw new \InvalidArgumentException("Invalid deduction_date for employee: {$row['employee_code']}");
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
        }
        return ['inserted' => $count, 'type' => 'deductions'];
    }

    // ── Incidents ─────────────────────────────────────────────────────────────

    private function insertIncidents(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $incidentDate = CsvNormalizer::normalizeDate($row['incident_date'] ?? null);

            if (! $incidentDate) {
                throw new \InvalidArgumentException("Invalid incident_date in row.");
            }

            $empId = null;
            if (! empty($row['employee_code'])) {
                try {
                    $empId = $this->resolveEmployeeId($row['employee_code'], $tenantId, $branchId);
                } catch (\RuntimeException) {
                    // Incidents can exist without a linked employee
                }
            }

            DB::table('incidents')->updateOrInsert(
                [
                    'tenant_id'     => $tenantId,
                    'branch_id'     => $branchId,
                    'employee_id'   => $empId,
                    'incident_date' => $incidentDate,
                ],
                [
                    'location'          => $row['location']          ?? null,
                    'injury_type'       => $row['injury_type']       ?? null,
                    'severity'          => $row['severity']          ?? 'low',
                    'cause'             => $row['root_cause']        ?? null,
                    'root_cause'        => $row['root_cause']        ?? null,
                    'corrective_action' => $row['corrective_action'] ?? null,
                    'preventive_action' => $row['preventive_action'] ?? null,
                    'medical_leave_days'=> CsvNormalizer::normalizeInt($row['medical_leave_days'] ?? null),
                    'description'       => $row['description']       ?? $row['injury_type'] ?? null,
                    'status'            => 'open',
                    'updated_at'        => now(),
                    'created_at'        => now(),
                ]
            );
            $count++;
        }
        return ['inserted' => $count, 'type' => 'incidents'];
    }

    // ── Hazard Register ───────────────────────────────────────────────────────

    private function insertHazardRegister(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            DB::table('hazard_register')->insert([
                'tenant_id'         => $tenantId,
                'branch_id'         => $branchId,
                'hazard_date'       => now()->toDateString(),
                'hazard_type'       => $row['hazard_type']       ?? 'General',
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
        }
        return ['inserted' => $count, 'type' => 'hazard_register'];
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

    // ── Contractors ───────────────────────────────────────────────────────────

    private function insertContractors(array $rows, int $tenantId, int $branchId): array
    {
        $count = 0;
        foreach ($rows as $row) {
            $name    = trim($row['contractor_name'] ?? '');
            $license = trim($row['license_number']  ?? '');

            if ($name === '') continue;

            // contractor_master: upsert by tenant+branch+license_number (unique key)
            // branch_id is required — FormXIIApiService filters by it
            DB::table('contractor_master')->updateOrInsert(
                [
                    'tenant_id'      => $tenantId,
                    'branch_id'      => $branchId,
                    'license_number' => $license ?: null,
                    'contractor_name'=> $name,
                ],
                [
                    'company_name'   => $name,
                    'company_type'   => 'contractor',
                    'contractor_code'=> $row['contractor_code'] ?? null,
                    'address'        => $row['address']         ?? null,
                    'company_address'=> $row['address']         ?? null,
                    'contact_person' => $row['contact_person']  ?? null,
                    'contact_number' => $row['mobile']          ?? null,
                    'phone'          => $row['mobile']          ?? null,
                    'email'          => $row['email']           ?? null,
                    'valid_from'     => CsvNormalizer::normalizeDate($row['valid_from'] ?? null) ?? now()->toDateString(),
                    'valid_to'       => CsvNormalizer::normalizeDate($row['valid_to']   ?? null) ?? now()->addYear()->toDateString(),
                    'license_no'     => $license ?: null,
                    'license_expiry' => CsvNormalizer::normalizeDate($row['valid_to']   ?? null),
                    'max_worker_limit'=> CsvNormalizer::normalizeInt($row['max_workers'] ?? null) ?: null,
                    'status'         => 'active',
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ]
            );

            // Also upsert into contractors table (CLRA license data)
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
        }
        return ['inserted' => $count, 'type' => 'contractors'];
    }
}
