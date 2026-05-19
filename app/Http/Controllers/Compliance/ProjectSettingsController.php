<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectSettingsController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branches = DB::table('branches')->where('tenant_id', $tenantId)->get();
        
        return view('compliance.settings.index', compact('tenant', 'branches'));
    }

    public function update(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $validated = $request->validate([
            'establishment_name' => 'required|string|max:255',
            'factory_license_no' => 'required|string|max:255',
            'pf_code' => 'nullable|string|max:255',
            'esi_code' => 'nullable|string|max:255',
            'labour_office_address' => 'nullable|string|max:500',
            'branches' => 'required|array',
            'branches.*.id' => 'required|exists:branches,id',
            'branches.*.unit_name' => 'required|string|max:255',
            'branches.*.address' => 'required|string',
        ]);

        DB::table('tenants')->where('id', $tenantId)->update([
            'establishment_name' => $validated['establishment_name'],
            'factory_license_no' => $validated['factory_license_no'],
            'pf_code' => $validated['pf_code'],
            'esi_code' => $validated['esi_code'],
            'labour_office_address' => $validated['labour_office_address'],
            'updated_at' => now(),
        ]);

        foreach ($validated['branches'] as $branchData) {
            DB::table('branches')
                ->where('id', $branchData['id'])
                ->where('tenant_id', $tenantId)
                ->update([
                    'unit_name' => $branchData['unit_name'],
                    'address' => $branchData['address'],
                    'updated_at' => now(),
                ]);
        }

        return redirect()->route('compliance.settings')
            ->with('success', 'Statutory settings updated successfully');
    }
}
