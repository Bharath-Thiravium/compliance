<?php

namespace App\Compliance\Builders;

abstract class BaseBuilder
{
    protected int $tenantId;
    protected int $branchId;
    protected int $month;
    protected int $year;

    public function __construct(
        protected \App\Compliance\Repositories\EmployeeRepository $employeeRepo,
        protected \App\Compliance\Repositories\PayrollRepository $payrollRepo,
        protected \App\Compliance\Repositories\AttendanceRepository $attendanceRepo,
        protected \App\Compliance\Repositories\ContractorRepository $contractorRepo,
        protected \App\Compliance\Repositories\IncidentRepository $incidentRepo,
        protected \App\Compliance\Repositories\BonusRepository $bonusRepo,
        protected \App\Compliance\Repositories\DeductionRepository $deductionRepo,
    ) {}

    public function build(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        return $this->getData();
    }

    abstract protected function getData(): array;

    protected function nilIfEmpty(array $data): array
    {
        return empty($data) ? ['status' => 'NIL'] : $data;
    }
}
