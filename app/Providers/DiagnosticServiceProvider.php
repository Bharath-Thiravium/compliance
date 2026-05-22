<?php

namespace App\Providers;

use App\Services\Compliance\Diagnostics\ComplianceDiagnosticEngine;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\ServiceProvider;

class DiagnosticServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ComplianceDiagnosticEngine::class, function ($app) {
            return new ComplianceDiagnosticEngine(
                $app->make(ComplianceOrchestrator::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
