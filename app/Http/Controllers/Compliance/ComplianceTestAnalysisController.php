<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Services\Compliance\Testing\ComplianceTestAnalyzer;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\Auth;

class ComplianceTestAnalysisController extends Controller
{
    public function testAnalysisReport(ComplianceOrchestrator $orchestrator)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $analyzer = new ComplianceTestAnalyzer($orchestrator);
        $report = $analyzer->runFullAnalysis();

        return view('compliance.dashboard.testanalysisreport', [
            'report' => $report,
            'user' => Auth::user()
        ]);
    }
}
