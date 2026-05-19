<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Compliance\Repositories\{
    EmployeeRepository,
    PayrollRepository,
    AttendanceRepository,
    ContractorRepository,
    IncidentRepository,
    BonusRepository,
    DeductionRepository,
};
use App\Compliance\ComplianceDataService;

class ComplianceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EmployeeRepository::class);
        $this->app->singleton(PayrollRepository::class);
        $this->app->singleton(AttendanceRepository::class);
        $this->app->singleton(ContractorRepository::class);
        $this->app->singleton(IncidentRepository::class);
        $this->app->singleton(BonusRepository::class);
        $this->app->singleton(DeductionRepository::class);
        $this->app->singleton(ComplianceDataService::class);
        
        // Core Services
        $this->app->singleton(\App\Services\Compliance\ComplianceOrchestrator::class);
        $this->app->singleton(\App\Services\Compliance\ComplianceExecutionService::class);
        $this->app->singleton(\App\Services\Compliance\BatchOrchestrator::class);
        $this->app->singleton(\App\Services\Compliance\FrequencyEngine::class);
        $this->app->singleton(\App\Services\Compliance\DataAvailabilityEngine::class);
        $this->app->singleton(\App\Services\Compliance\BatchReviewService::class);
        $this->app->singleton(\App\Services\Compliance\ComplianceTimelineService::class);
        $this->app->singleton(\App\Services\Compliance\ComplianceHealthService::class);
        
        // Validation Services
        $this->app->singleton(\App\Services\Compliance\StrictDataValidator::class);
        $this->app->singleton(\App\Services\Compliance\PayrollValidationGuard::class);
        $this->app->singleton(\App\Services\Compliance\ProductionValidationGuard::class);
        
        // Form Services
        $this->app->singleton(\App\Services\Compliance\FormDataAggregator::class);
        $this->app->singleton(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);
        $this->app->singleton(\App\Services\Compliance\FormApis\FormApiServiceFactory::class);
        
        // Audit Services
        $this->app->singleton(\App\Services\Compliance\Audit\ComplianceAuditService::class);
        $this->app->singleton(\App\Services\Compliance\Audit\ComplianceCorrectionService::class);
        $this->app->singleton(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
    }

    public function boot(): void
    {
        //
    }
}
