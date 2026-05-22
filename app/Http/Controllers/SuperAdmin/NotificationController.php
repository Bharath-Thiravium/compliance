<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceGenerationLog;
use App\Models\AuditLog;
use App\Services\GovernmentFormUpdateService;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(protected GovernmentFormUpdateService $updateService) {}

    public function getFormUpdates()
    {
        try {
            $updates = $this->updateService->getActiveUpdates(10);

            return response()->json([
                'success' => true,
                'count'   => $updates->count(),
                'items'   => $updates->map(fn($u) => [
                    'form_code'         => $u->form_code ?? 'N/A',
                    'form_name'         => $u->form_name ?? 'N/A',
                    'source_department' => $u->source_department ?? 'N/A',
                    'source_domain'     => $u->source_domain ?? 'N/A',
                    'update_message'    => $u->update_message ?? 'N/A',
                    'published_on'      => $u->published_on ? (is_string($u->published_on) ? $u->published_on : $u->published_on->format('d M Y')) : 'N/A',
                    'source_url'        => $u->source_url ?? '#',
                    'source_type'       => $u->source_type ?? 'N/A',
                    'official_status'   => $u->official_status ?? 'N/A',
                    'section'           => $u->section ?? 'N/A',
                    'status'            => $u->status ?? 'N/A',
                    'updated_at'        => $u->updated_at ?? 'N/A',
                    'is_new'            => $u->is_new ?? false,
                ])->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Form notifications fetch failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'count' => 0, 'items' => []], 200);
        }
    }

    public function getSystemErrors()
    {
        try {
            $failures      = ComplianceGenerationLog::whereNotNull('error_message')->latest()->limit(10)->get();
            $auditFailures = AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'failed'")->latest('created_at')->limit(5)->get();

            $count = ComplianceGenerationLog::whereNotNull('error_message')->count()
                   + AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'failed'")->count();

            $items = [];
            foreach ($failures as $f) {
                $items[] = ['action_type' => 'Generation Failed', 'form_code' => $f->form_code ?? 'N/A', 'batch_id' => $f->batch_id ?? 'N/A', 'error_message' => substr($f->error_message ?? '', 0, 60), 'time' => $f->created_at ? $f->created_at->diffForHumans() : 'N/A'];
            }
            foreach ($auditFailures as $f) {
                $items[] = ['action_type' => $f->action ?? 'Action Failed', 'form_code' => $f->form_code ?? 'N/A', 'batch_id' => $f->batch_id ?? 'N/A', 'error_message' => substr($f->errorMessage ?? '', 0, 60), 'time' => $f->created_at ? $f->created_at->diffForHumans() : 'N/A'];
            }

            return response()->json(['success' => true, 'count' => $count, 'items' => array_slice($items, 0, 10)]);
        } catch (\Exception $e) {
            Log::error('System errors fetch failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'count' => 0, 'items' => []], 200);
        }
    }
}
