<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Services\Compliance\ComplianceOrchestrator;
use App\Services\Compliance\Testing\ComplianceTestAnalyzer;
use Illuminate\Http\Request;

class ComplianceTestAnalysisController extends Controller
{
    public function testAnalysisReport(Request $request, ComplianceOrchestrator $orchestrator)
    {
        // Token-protect so it can't be hit by anyone on production
        $token = (string) config('app.ops_token', '');
        if ($token !== '' && !hash_equals($token, (string) $request->query('token', ''))) {
            abort(403, 'Provide ?token=YOUR_OPS_TOKEN');
        }

        $report = (new ComplianceTestAnalyzer($orchestrator))->runFullAnalysis();

        return view('compliance.dashboard.testanalysisreport', compact('report'));
    }
}
