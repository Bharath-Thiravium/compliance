<?php

namespace App\Http\Controllers;

use App\Services\Compliance\ManualStatutoryDataRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManualDataController extends Controller
{
    public function __construct(
        private ManualStatutoryDataRepository $repository
    ) {}

    public function show(int $month, int $year)
    {
        $user = Auth::user();
        
        if ($user->tenant->subscription_type !== 'MINIMAL') {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Manual data entry is only for MINIMAL subscription.');
        }

        $data = $this->repository->get($user->tenant_id, $month, $year);

        return view('compliance.manual_data_entry', compact('data', 'month', 'year'));
    }

    public function save(Request $request, int $month, int $year)
    {
        $user = Auth::user();
        
        if ($user->tenant->subscription_type !== 'MINIMAL') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'establishment' => 'nullable|array',
            'employer' => 'nullable|array',
            'employees' => 'nullable|array',
            'wages' => 'nullable|array',
            'attendance' => 'nullable|array',
            'accidents' => 'nullable|array',
            'contractors' => 'nullable|array',
        ]);

        $this->repository->save($user->tenant_id, $month, $year, $validated);

        return response()->json(['success' => true, 'message' => 'Data saved successfully']);
    }
}
