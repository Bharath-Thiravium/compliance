<?php

namespace App\Services\Compliance;

use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceStatus;
use App\Models\ComplianceGenerationLog;
use App\Models\WorkforcePayrollCycle;
use Illuminate\Support\Facades\DB;
use Exception;

class ComplianceEngine
{
    public function __construct(
        private FormDataAggregator $aggregator,
        private ComplianceLockService $lockService
    ) {}

    public function generateForm(int $formId, string $periodFrom, string $periodTo, ?int $branchId = null, ?string $revisionReason = null, ?int $tenantId = null): array
    {
        DB::beginTransaction();
        try {
            $form = ComplianceFormsMaster::findOrFail($formId);
            
            $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : 1);
            
            if (!$this->checkSubscription($form->act_type)) {
                throw new Exception("Module {$form->act_type} is not enabled for this tenant");
            }

            $this->validatePrerequisites($form, $periodFrom, $periodTo, $tenantId);

            $existingStatus = ComplianceStatus::where([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'form_id' => $formId,
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
            ])->orderBy('version_number', 'desc')->first();

            if ($existingStatus && $existingStatus->isLocked() && !$revisionReason) {
                throw new Exception('This compliance form is locked. Provide revision_reason to create new version');
            }

            $complianceStatus = ComplianceStatus::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'form_id' => $formId,
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'status' => 'Pending',
                'version_number' => $existingStatus ? $existingStatus->version_number + 1 : 1,
                'is_revised' => $existingStatus ? true : false,
                'revised_from_id' => $existingStatus?->id,
                'revision_reason' => $revisionReason,
            ]);

            $data = $this->aggregator->aggregateData($form, $periodFrom, $periodTo, $branchId);
            
            $filePath = $this->generatePDF($form, $data);
            $checksum = hash_file('sha256', storage_path('app/' . $filePath));

            $log = ComplianceGenerationLog::create([
                'tenant_id' => $tenantId,
                'form_id' => $formId,
                'compliance_status_id' => $complianceStatus->id,
                'generated_by' => auth()->id() ?? 1,
                'file_path' => $filePath,
                'checksum_hash' => $checksum,
                'generated_snapshot' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $complianceStatus->update([
                'status' => 'Generated',
                'generated_at' => now(),
            ]);

            $this->lockService->lockAfterGeneration($complianceStatus);

            DB::commit();
            return ['success' => true, 'file_path' => $filePath, 'log_id' => $log->id, 'version' => $complianceStatus->version_number];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function validatePrerequisites(ComplianceFormsMaster $form, string $periodFrom, string $periodTo, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : 1);
        
        if ($form->frequency === 'Monthly') {
            $cycle = WorkforcePayrollCycle::where('tenant_id', $tenantId)
                ->where('period_from', $periodFrom)
                ->where('period_to', $periodTo)
                ->first();

            if (!$cycle || $cycle->status !== 'locked') {
                throw new Exception('Payroll cycle must be locked before generating compliance forms');
            }
        }
    }

    public function lockForm(int $complianceStatusId): bool
    {
        return $this->lockService->lockAfterGeneration(
            ComplianceStatus::findOrFail($complianceStatusId)
        );
    }

    public function markAsNIL(int $complianceStatusId, string $reason): bool
    {
        $status = ComplianceStatus::findOrFail($complianceStatusId);
        
        if ($status->isLocked()) {
            throw new Exception('Cannot mark locked form as NIL');
        }

        return $status->update(['status' => 'NIL']);
    }

    public function validateFormSubscription(int $tenantId, ComplianceFormsMaster $form): bool
    {
        return $this->checkSubscription($form->act_type);
    }

    public function checkSubscription(string $actType): bool
    {
        $tenantId = auth()->check() ? auth()->user()->tenant_id : 1;
        
        $moduleMap = [
            'Factories' => 'workforce',
            'CLRA' => 'clra',
            'Shops' => 'shops',
            'EPF' => 'epf',
            'ESI' => 'esi',
        ];

        $moduleName = $moduleMap[$actType] ?? null;
        if (!$moduleName) return false;

        return DB::table('tenant_services')
            ->where('tenant_id', $tenantId)
            ->where('module_name', $moduleName)
            ->where('is_enabled', true)
            ->exists();
    }

    public function calculateDueDate(ComplianceFormsMaster $form, string $periodTo): string
    {
        $date = \Carbon\Carbon::parse($periodTo);
        
        if ($form->due_day && $form->due_month) {
            return \Carbon\Carbon::create(null, $form->due_month, $form->due_day)->format('Y-m-d');
        }
        
        if ($form->due_day) {
            $dueDate = $date->copy()->addMonth()->day($form->due_day);
        } else {
            $dueDate = match($form->frequency) {
                'Monthly' => $date->addDays(10),
                'Annual' => $date->addDays(30),
                'HalfYearly' => $date->addDays(15),
                default => $date->addDays(7),
            };
        }
        
        if ($form->grace_days) {
            $dueDate->addDays($form->grace_days);
        }
        
        return $dueDate->format('Y-m-d');
    }

    private function generatePDF(ComplianceFormsMaster $form, array $data): string
    {
        // Use FormGenerator if available
        $generator = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::make($form->form_code);
        
        if ($generator) {
            // Generator will handle PDF creation
            return $form->form_code; // Placeholder
        }
        
        // Fallback to JSON snapshot
        $fileName = "compliance_{$form->form_code}_" . time() . ".pdf";
        $filePath = "compliance/{$fileName}";
        
        file_put_contents(
            storage_path("app/{$filePath}.json"),
            json_encode($data, JSON_PRETTY_PRINT)
        );
        
        return $filePath;
    }
}
