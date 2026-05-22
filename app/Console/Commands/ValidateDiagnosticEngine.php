<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidateDiagnosticEngine extends Command
{
    protected $signature = 'compliance:validate-diagnostics';
    protected $description = 'Validate Deep Diagnostic Engine implementation';

    public function handle(): int
    {
        $this->info('Validating Deep Diagnostic Engine Implementation...');
        $this->newLine();

        $checks = [
            'Engine Class' => $this->checkEngineClass(),
            'Controller' => $this->checkController(),
            'Command' => $this->checkCommand(),
            'Service Provider' => $this->checkServiceProvider(),
            'Dashboard View' => $this->checkDashboardView(),
            'Routes' => $this->checkRoutes(),
            'Documentation' => $this->checkDocumentation(),
        ];

        $passed = 0;
        $failed = 0;

        $this->table(['Component', 'Status'], array_map(function($name, $result) use (&$passed, &$failed) {
            if ($result) {
                $passed++;
                return [$name, '✓ PASS'];
            } else {
                $failed++;
                return [$name, '✗ FAIL'];
            }
        }, array_keys($checks), $checks));

        $this->newLine();
        $this->info("Validation Complete: {$passed} passed, {$failed} failed");

        return $failed === 0 ? 0 : 1;
    }

    private function checkEngineClass(): bool
    {
        $path = app_path('Services/Compliance/Diagnostics/ComplianceDiagnosticEngine.php');
        if (!File::exists($path)) {
            $this->error("  ✗ Engine class not found at {$path}");
            return false;
        }

        $content = File::get($path);
        $required = [
            'class ComplianceDiagnosticEngine',
            'runFullDiagnostics',
            'testPreviewPipeline',
            'testGeneratorAnalysis',
            'testBladeTemplateAnalysis',
            'testApiServiceAnalysis',
            'testDatabaseDatasets',
            'testPdfGeneration',
            'testInspectionPack',
            'testSecurityIsolation',
            'calculateHealthScore',
        ];

        foreach ($required as $method) {
            if (strpos($content, $method) === false) {
                $this->error("  ✗ Missing method: {$method}");
                return false;
            }
        }

        $this->line('  ✓ Engine class valid');
        return true;
    }

    private function checkController(): bool
    {
        $path = app_path('Http/Controllers/Compliance/ComplianceDiagnosticController.php');
        if (!File::exists($path)) {
            $this->error("  ✗ Controller not found at {$path}");
            return false;
        }

        $content = File::get($path);
        $required = [
            'class ComplianceDiagnosticController',
            'runDiagnostics',
            'getLatestReport',
            'getDashboardData',
        ];

        foreach ($required as $method) {
            if (strpos($content, $method) === false) {
                $this->error("  ✗ Missing method: {$method}");
                return false;
            }
        }

        $this->line('  ✓ Controller valid');
        return true;
    }

    private function checkCommand(): bool
    {
        $path = app_path('Console/Commands/RunComplianceDiagnostics.php');
        if (!File::exists($path)) {
            $this->error("  ✗ Command not found at {$path}");
            return false;
        }

        $content = File::get($path);
        if (strpos($content, 'class RunComplianceDiagnostics') === false) {
            $this->error("  ✗ Command class not found");
            return false;
        }

        $this->line('  ✓ Command valid');
        return true;
    }

    private function checkServiceProvider(): bool
    {
        $path = app_path('Providers/DiagnosticServiceProvider.php');
        if (!File::exists($path)) {
            $this->error("  ✗ Service provider not found at {$path}");
            return false;
        }

        $providerPath = base_path('bootstrap/providers.php');
        $providerContent = File::get($providerPath);
        if (strpos($providerContent, 'DiagnosticServiceProvider') === false) {
            $this->error("  ✗ Service provider not registered in bootstrap/providers.php");
            return false;
        }

        $this->line('  ✓ Service provider valid');
        return true;
    }

    private function checkDashboardView(): bool
    {
        $path = resource_path('views/compliance/dashboard/testanalysisreport.blade.php');
        if (!File::exists($path)) {
            $this->error("  ✗ Dashboard view not found at {$path}");
            return false;
        }

        $content = File::get($path);
        $required = [
            'health_score',
            'Component Diagnostics',
            'Root Cause Analysis',
            'copyDiagnosticsToClipboard',
        ];

        foreach ($required as $element) {
            if (strpos($content, $element) === false) {
                $this->error("  ✗ Missing element: {$element}");
                return false;
            }
        }

        $this->line('  ✓ Dashboard view valid');
        return true;
    }

    private function checkRoutes(): bool
    {
        $path = base_path('routes/compliance.php');
        $content = File::get($path);

        $required = [
            'ComplianceDiagnosticController',
            '/diagnostics/run',
            '/diagnostics/latest',
            '/diagnostics/dashboard',
        ];

        foreach ($required as $route) {
            if (strpos($content, $route) === false) {
                $this->error("  ✗ Missing route: {$route}");
                return false;
            }
        }

        $this->line('  ✓ Routes valid');
        return true;
    }

    private function checkDocumentation(): bool
    {
        $files = [
            'DEEP_DIAGNOSTIC_ENGINE_GUIDE.md',
            'DIAGNOSTIC_ENGINE_QUICK_REFERENCE.md',
            'DEEP_DIAGNOSTIC_ENGINE_IMPLEMENTATION.md',
        ];

        foreach ($files as $file) {
            if (!File::exists(base_path($file))) {
                $this->error("  ✗ Documentation missing: {$file}");
                return false;
            }
        }

        $this->line('  ✓ Documentation valid');
        return true;
    }
}
