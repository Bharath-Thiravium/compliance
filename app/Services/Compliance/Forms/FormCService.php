<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\Compliance\Debug\FormDebugger;

class FormCService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_C');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month    = $month;
        $this->year     = $year;

        $advances = DB::table('workforce_advances as a')
            ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.branch_id', $branchId)
            ->whereYear('a.advance_date', $year)
            ->whereMonth('a.advance_date', $month)
            ->select([
                'e.name as employee_name',
                DB::raw("'Advance' as recovery_type"),
                DB::raw("'' as particulars"),
                DB::raw("'' as damage_date"),
                'a.amount',
                DB::raw("'' as show_cause"),
                DB::raw("'' as explanation"),
                'a.num_instalments as installments',
                'a.first_month',
                'a.last_month',
                DB::raw("'' as recovery_date"),
                'a.remarks',
            ])
            ->get()->map(fn($r) => (array)$r)->toArray();

        $fines = DB::table('workforce_fines as f')
            ->join('workforce_employee as e', 'e.id', '=', 'f.employee_id')
            ->where('f.tenant_id', $tenantId)
            ->where('f.branch_id', $branchId)
            ->whereYear('f.fine_date', $year)
            ->whereMonth('f.fine_date', $month)
            ->select([
                'e.name as employee_name',
                DB::raw("'Fine' as recovery_type"),
                'f.reason as particulars',
                'f.fine_date as damage_date',
                'f.amount',
                DB::raw("'' as show_cause"),
                DB::raw("'' as explanation"),
                DB::raw("1 as installments"),
                DB::raw("'' as first_month"),
                DB::raw("'' as last_month"),
                DB::raw("'' as recovery_date"),
                'f.remarks',
            ])
            ->get()->map(fn($r) => (array)$r)->toArray();

        $rows = array_merge($advances, $fines);

        FormDebugger::end('FORM_C', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        return $this->buildResponse($rows);
    }
}
