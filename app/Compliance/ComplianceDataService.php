<?php

namespace App\Compliance;

use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class ComplianceDataService
{
    public function __construct(
        private ComplianceOrchestrator $orchestrator,
        private \App\Compliance\Repositories\EmployeeRepository $employeeRepo,
        private \App\Compliance\Repositories\PayrollRepository $payrollRepo,
        private \App\Compliance\Repositories\AttendanceRepository $attendanceRepo,
        private \App\Compliance\Repositories\ContractorRepository $contractorRepo,
        private \App\Compliance\Repositories\IncidentRepository $incidentRepo,
        private \App\Compliance\Repositories\BonusRepository $bonusRepo,
        private \App\Compliance\Repositories\DeductionRepository $deductionRepo,
    ) {}

    public function buildFormData(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        Log::info("Building form", [
            'form' => $formCode,
            'tenant' => $tenantId,
            'branch' => $branchId,
            'month' => $month,
            'year' => $year,
        ]);

        $employeeCount = \Illuminate\Support\Facades\DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->count();
            
        if ($employeeCount === 0) {
            \Illuminate\Support\Facades\Artisan::call('compliance:generate-demo-dataset', [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
                '--employees' => 20
            ]);
            Log::info("Auto-generated demo dataset for tenant {$tenantId}");
        }

        try {
            $result = $this->orchestrator->execute($tenantId, $branchId, $month, $year, $formCode, 'preview');
            
            if ($result['status'] === 'success' && isset($result['result']['html'])) {
                return $this->normalizeData([
                    'rows' => [],
                    'header' => [],
                    'totals' => [],
                    'is_nil' => false,
                    'html' => $result['result']['html']
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Orchestrator execution failed: " . $e->getMessage());
        }

        return $this->normalizeData(['status' => 'NIL', 'error' => 'Form generation failed']);
    }

    public function renderForm(string $formCode, int $tenantId, int $branchId, int $month, int $year): string
    {
        try {
            $result = $this->orchestrator->execute($tenantId, $branchId, $month, $year, $formCode, 'preview');
            
            if ($result['status'] === 'success' && isset($result['result']['html'])) {
                return $result['result']['html'];
            }
        } catch (\Exception $e) {
            Log::error("Form rendering failed: " . $e->getMessage());
            return "Error rendering form: " . $e->getMessage();
        }

        return "Unable to render form {$formCode}";
    }

    private function normalizeData(array $data): array
    {
        if (!isset($data['header'])) {
            $data['header'] = [];
        }

        if (isset($data['entries']) && !isset($data['rows'])) {
            $data['rows'] = $data['entries'];
        }
        if (isset($data['rows']) && !isset($data['entries'])) {
            $data['entries'] = $data['rows'];
        }

        if (!isset($data['rows'])) {
            $data['rows'] = [];
        }
        if (!isset($data['entries'])) {
            $data['entries'] = [];
        }

        if (!isset($data['totals'])) {
            $data['totals'] = [];
        }

        if (!isset($data['period'])) {
            $data['period'] = '';
        }

        $data['is_nil'] = ($data['status'] ?? '') === 'NIL';

        return $data;
    }
}
