<?php

namespace App\Http\Controllers\Compliance;

use App\Services\Compliance\Diagnostics\ComplianceDiagnosticEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplianceDiagnosticController
{
    public function __construct(private ComplianceDiagnosticEngine $diagnosticEngine) {}

    public function runDiagnostics()
    {
        $report = $this->diagnosticEngine->runFullDiagnostics();

        // Store report for dashboard
        $filename = 'diagnostic_report_' . now()->format('Y-m-d_H-i-s') . '.json';
        Storage::disk('local')->put("logs/{$filename}", json_encode($report, JSON_PRETTY_PRINT));

        return response()->json($report);
    }

    public function getLatestReport()
    {
        $files = Storage::disk('local')->files('logs');
        $diagnosticFiles = array_filter($files, fn($f) => strpos($f, 'diagnostic_report_') === 0);

        if (empty($diagnosticFiles)) {
            return response()->json(['error' => 'No diagnostic reports found'], 404);
        }

        $latestFile = end($diagnosticFiles);
        $content = Storage::disk('local')->get($latestFile);

        return response()->json(json_decode($content, true));
    }

    public function getDashboardData()
    {
        $report = $this->diagnosticEngine->runFullDiagnostics();

        return response()->json([
            'health_score' => $report['health_score'],
            'status' => $report['status'],
            'summary' => $report['summary'],
            'diagnostics' => $report['diagnostics'],
            'root_causes' => array_slice($report['root_causes'], 0, 10),
            'execution_time' => $report['execution_time'],
            'timestamp' => $report['timestamp']
        ]);
    }
}
