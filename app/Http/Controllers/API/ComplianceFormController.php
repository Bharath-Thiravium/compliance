<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Compliance\Forms\Form10Service;
use App\Services\Compliance\Forms\Form12Service;
use App\Services\Compliance\Forms\Form17Service;
use App\Services\Compliance\Forms\Form25Service;
use App\Services\Compliance\Forms\FormBService;
use App\Services\Compliance\Forms\Form26Service;
use App\Services\Compliance\Forms\Form26AService;
use App\Services\Compliance\Forms\HazardRegisterService;
use App\Services\Compliance\Forms\FormXIIService;
use App\Services\Compliance\Forms\FormXIIIService;
use App\Services\Compliance\Forms\FormXIVService;
use App\Services\Compliance\Forms\FormXVIService;
use App\Services\Compliance\Forms\FormXVIIService;
use App\Services\Compliance\Forms\FormXVIIIService;
use App\Services\Compliance\Forms\FormXIXService;
use App\Services\Compliance\Forms\FormXXService;
use App\Services\Compliance\Forms\FormXXIService;
use App\Services\Compliance\Forms\FormXXIIService;
use App\Services\Compliance\Forms\FormXXIIIService;
use App\Services\Compliance\Forms\FormAService;
use App\Services\Compliance\Forms\FormCService;
use App\Services\Compliance\Forms\FormDService;
use App\Services\Compliance\Forms\FormDERService;
use App\Services\Compliance\Forms\Form2Service;
use App\Services\Compliance\Forms\Form8Service;
use App\Services\Compliance\Forms\Form11Service;
use App\Services\Compliance\Forms\Form18Service;
use App\Services\Compliance\Forms\EsiForm12Service;
use App\Services\Compliance\Forms\EpfInspectionService;
use App\Services\Compliance\Forms\ShopsFormCService;
use App\Services\Compliance\Forms\ShopsUnpaidService;
use App\Services\Compliance\Forms\ShopsForm12Service;
use App\Services\Compliance\Forms\ShopsForm13Service;
use App\Services\Compliance\Forms\ShopsFinesService;
use App\Services\Compliance\Forms\ShopsFormVIService;
use Illuminate\Http\Request;

class ComplianceFormController extends Controller
{
    public function form10(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form10Service();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function form12(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form12Service();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function form17(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form17Service();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function form25(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form25Service();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function formB(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormBService();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function form26(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form26Service();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function form26A(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form26AService();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function hazard(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new HazardRegisterService();
        $data = $service->generate($tenantId, $branchId, $month, $year);

        return response()->json($data);
    }

    public function formXII(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXIIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXIII(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXIIIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXIV(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXIVService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXVI(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXVIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXVII(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXVIIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXVIII(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXVIIIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXIX(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXIXService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXX(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXXService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXXI(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXXIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXXII(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXXIIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formXXIII(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormXXIIIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formA(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormAService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formC(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormCService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formD(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormDService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function formDER(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new FormDERService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function form2(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form2Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function form8(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form8Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function form11(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form11Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function form18(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new Form18Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function esiForm12(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new EsiForm12Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function epfInspection(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new EpfInspectionService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function shopsFormC(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new ShopsFormCService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function shopsUnpaid(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new ShopsUnpaidService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function shopsForm12(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new ShopsForm12Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function shopsForm13(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new ShopsForm13Service();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function shopsFines(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new ShopsFinesService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }

    public function shopsFormVI(Request $request)
    {
        $tenantId = $request->query('tenant_id', auth()->user()?->tenant_id ?? 1);
        $branchId = $request->query('branch_id', 1);
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $service = new ShopsFormVIService();
        return response()->json($service->generate($tenantId, $branchId, $month, $year));
    }
}
