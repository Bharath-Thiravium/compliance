<?php

namespace App\Compliance\Builders;

class WorkShiftBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $employees->map(fn($emp) => [
                'employee_code' => $emp->employee_code ?? 'N/A',
                'employee_name' => $emp->name ?? 'N/A',
                'shift_start' => $emp->shift_start ?? 'N/A',
                'shift_end' => $emp->shift_end ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class InspectionRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $incidents->map(fn($incident) => [
                'incident_date' => $incident->incident_date ?? 'N/A',
                'employee_name' => $incident->employee->name ?? 'N/A',
                'incident_type' => $incident->incident_type ?? 'N/A',
                'description' => $incident->description ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class AccidentRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $incidents->map(fn($incident) => [
                'sl_no' => $incident->id,
                'employee_name' => $incident->employee->name ?? 'N/A',
                'incident_date' => $incident->incident_date ?? 'N/A',
                'place' => $incident->place ?? 'N/A',
                'description' => $incident->description ?? 'N/A',
                'injury_type' => $incident->injury_type ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class HealthRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $employees->map(fn($emp) => [
                'employee_code' => $emp->employee_code ?? 'N/A',
                'employee_name' => $emp->name ?? 'N/A',
                'health_status' => 'N/A',
            ])->toArray(),
        ];
    }
}

class AccidentReportBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $incident = $incidents->first();
        
        return [
            'registration_number' => 'N/A',
            'factory_name' => 'N/A',
            'employee_name' => $incident->employee->name ?? 'N/A',
            'incident_date' => $incident->incident_date ?? 'N/A',
            'description' => $incident->description ?? 'N/A',
            'injury_type' => $incident->injury_type ?? 'N/A',
        ];
    }
}

class DangerousOccurrenceBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $incidents->map(fn($incident) => [
                'sl_no' => $incident->id,
                'occurrence_date' => $incident->incident_date ?? 'N/A',
                'place' => $incident->place ?? 'N/A',
                'description' => $incident->description ?? 'N/A',
                'damage' => $incident->damage ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class ContractorMasterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $contractors = $this->contractorRepo->getContractors($this->tenantId);
        
        if ($contractors->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'entries' => $contractors->map(fn($contractor) => [
                'contractor_name' => $contractor->name ?? 'N/A',
                'address' => $contractor->address ?? 'N/A',
                'license_number' => $contractor->license_number ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class EmploymentCardBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $deployments->map(fn($dep) => [
                'employee_name' => $dep->employee->name ?? 'N/A',
                'contractor_name' => $dep->contractor->name ?? 'N/A',
                'deployment_date' => $dep->deployment_start ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class ContractorMusterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $deployments->map(fn($dep) => [
                'employee_name' => $dep->employee->name ?? 'N/A',
                'contractor_name' => $dep->contractor->name ?? 'N/A',
                'deployment_date' => $dep->deployment_start ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class ContractorWageRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $deployments->map(fn($dep) => [
                'employee_name' => $dep->employee->name ?? 'N/A',
                'contractor_name' => $dep->contractor->name ?? 'N/A',
                'wage_amount' => $dep->wage_amount ?? 0,
            ])->toArray(),
        ];
    }
}

class ContractorWageSlipBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $deployments->map(fn($dep) => [
                'employee_name' => $dep->employee->name ?? 'N/A',
                'contractor_name' => $dep->contractor->name ?? 'N/A',
                'wage_amount' => $dep->wage_amount ?? 0,
            ])->toArray(),
        ];
    }
}

class FinesRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $fines = $this->deductionRepo->getFines($this->tenantId, $this->month, $this->year);
        
        if ($fines->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $fines->map(fn($fine) => [
                'employee_name' => $fine->employee->name ?? 'N/A',
                'fine_amount' => $fine->fines ?? 0,
                'reason' => 'N/A',
            ])->toArray(),
            'total_fines' => $fines->sum('fines'),
        ];
    }
}

class AdvanceRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $advances = $this->deductionRepo->getAdvances($this->tenantId, $this->month, $this->year);
        
        if ($advances->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $advances->map(fn($adv) => [
                'employee_name' => $adv->employee->name ?? 'N/A',
                'advance_amount' => $adv->advances ?? 0,
                'date' => $adv->payment_date ?? 'N/A',
            ])->toArray(),
            'total_advances' => $advances->sum('advances'),
        ];
    }
}

class ContractorOvertimeBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $deployments->map(fn($dep) => [
                'employee_name' => $dep->employee->name ?? 'N/A',
                'overtime_hours' => $dep->overtime_hours ?? 0,
                'overtime_wages' => $dep->overtime_wages ?? 0,
            ])->toArray(),
        ];
    }
}

class ContractorHalfYearlyBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $contractors = $this->contractorRepo->getContractors($this->tenantId);
        
        if ($contractors->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $contractors->map(fn($contractor) => [
                'contractor_name' => $contractor->name ?? 'N/A',
                'total_workmen' => 0,
                'total_wages' => 0,
            ])->toArray(),
        ];
    }
}

class PrincipalAnnualBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $contractors = $this->contractorRepo->getContractors($this->tenantId);
        
        if ($contractors->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $contractors->map(fn($contractor) => [
                'contractor_name' => $contractor->name ?? 'N/A',
                'total_workmen' => 0,
                'total_wages' => 0,
            ])->toArray(),
        ];
    }
}

class ShopsWageRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $entries = $this->payrollRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($entries->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $entries->map(fn($entry) => [
                'employee_name' => $entry->employee->name ?? 'N/A',
                'gross_salary' => $entry->gross_salary ?? 0,
                'deductions' => $entry->total_deductions ?? 0,
                'net_salary' => $entry->net_salary ?? 0,
            ])->toArray(),
            'total_gross' => $entries->sum('gross_salary'),
        ];
    }
}

class ShopsLeaveRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $employees->map(fn($emp) => [
                'employee_name' => $emp->name ?? 'N/A',
                'leave_days' => 0,
                'balance' => 0,
            ])->toArray(),
        ];
    }
}

class ShopsEmployeeRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'entries' => $employees->map(fn($emp) => [
                'employee_code' => $emp->employee_code ?? 'N/A',
                'employee_name' => $emp->name ?? 'N/A',
                'designation' => $emp->designation ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class ShopsHolidayRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $employees->map(fn($emp) => [
                'employee_name' => $emp->name ?? 'N/A',
                'holidays' => [],
            ])->toArray(),
        ];
    }
}

class ShopsFinesRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $fines = $this->deductionRepo->getFines($this->tenantId, $this->month, $this->year);
        
        if ($fines->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $fines->map(fn($fine) => [
                'employee_name' => $fine->employee->name ?? 'N/A',
                'fine_amount' => $fine->fines ?? 0,
            ])->toArray(),
            'total_fines' => $fines->sum('fines'),
        ];
    }
}

class ShopsUnpaidBonusBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $unpaid = $this->bonusRepo->getUnpaid($this->tenantId, $this->month, $this->year);
        
        if ($unpaid->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $unpaid->map(fn($bonus) => [
                'employee_name' => $bonus->employee->name ?? 'N/A',
                'bonus_amount' => $bonus->bonus_amount ?? 0,
            ])->toArray(),
            'total_unpaid' => $unpaid->sum('bonus_amount'),
        ];
    }
}

class EqualRemunerationBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $employees->map(fn($emp) => [
                'employee_name' => $emp->name ?? 'N/A',
                'designation' => $emp->designation ?? 'N/A',
                'salary' => 0,
            ])->toArray(),
        ];
    }
}

class OvertimeRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $entries = $this->payrollRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($entries->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $entries->map(fn($entry) => [
                'employee_code' => $entry->employee->employee_code ?? 'N/A',
                'employee_name' => $entry->employee->name ?? 'N/A',
                'overtime_hours' => $entry->overtime_hours ?? 0,
                'overtime_wages' => $entry->overtime_wages ?? 0,
            ])->toArray(),
            'total_overtime_wages' => $entries->sum('overtime_wages'),
        ];
    }
}

class EmployeeRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'entries' => $employees->map(fn($emp) => [
                'employee_code' => $emp->employee_code ?? 'N/A',
                'employee_name' => $emp->name ?? 'N/A',
                'designation' => $emp->designation ?? 'N/A',
                'date_of_birth' => $emp->date_of_birth ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class IncidentBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $incidents->map(fn($incident) => [
                'incident_date' => $incident->incident_date ?? 'N/A',
                'employee_name' => $incident->employee->name ?? 'N/A',
                'description' => $incident->description ?? 'N/A',
            ])->toArray(),
        ];
    }
}

class DeductionRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deductions = $this->deductionRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deductions->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'period' => "{$this->month}/{$this->year}",
            'entries' => $deductions->map(fn($ded) => [
                'employee_name' => $ded->employee->name ?? 'N/A',
                'pf_deduction' => $ded->pf_employee ?? 0,
                'esi_deduction' => $ded->esi_employee ?? 0,
                'other_deductions' => $ded->other_deductions ?? 0,
                'total_deductions' => $ded->total_deductions ?? 0,
            ])->toArray(),
            'total_deductions' => $deductions->sum('total_deductions'),
        ];
    }
}
