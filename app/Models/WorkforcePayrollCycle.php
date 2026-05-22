<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WorkforcePayrollCycle extends Model
{
    use SoftDeletes;

    protected $table = 'workforce_payroll_cycle';

    protected $fillable = [
        'tenant_id',
        'cycle_name',
        'period_from',
        'period_to',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $query->where('tenant_id', Auth::user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && !$model->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(WorkforcePayrollEntry::class, 'payroll_cycle_id');
    }
    public function processBatch(int $batchId): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $batch = ComplianceExecutionBatch::where('tenant_id', $user->tenant_id)
            ->where('id', $batchId)
            ->with('section')
            ->firstOrFail();

        $batch->update(['status' => 'processing']);

        $tenantId = $user->tenant_id;
        $subscription = strtoupper(trim($user->tenant->subscription_type ?? ''));
        $isFull = $subscription === 'FULL';

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ STRICT PAYROLL VALIDATION (SCHEMA ALIGNED)
    |--------------------------------------------------------------------------
    */

        $payrollExists = \App\Models\WorkforcePayrollCycle::query()
            ->whereDate('period_from', $batch->period_from)
            ->whereDate('period_to', $batch->period_to)
            ->where('status', 'processed')
            ->exists();

        if (!$payrollExists) {
            $batch->update([
                'status' => 'failed',
                'processed_at' => now(),
            ]);

            throw new \Exception(
                "Payroll not processed for period {$batch->period_from} to {$batch->period_to}."
            );
        }

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ FORM GENERATION LOOP
    |--------------------------------------------------------------------------
    */

        $results = [];
        $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);

        foreach ($batch->form_ids as $formId) {

            try {

                $form = ComplianceFormsMaster::findOrFail($formId);
                $generator = $factory::make($form->form_code);

                if (!$generator) {
                    throw new \Exception("No generator found for {$form->form_code}");
                }

                $pdfContent = $generator->generate(
                    $batch->tenant_id,
                    $batch->branch_id ?? 1,
                    $batch->period_month,
                    $batch->period_year,
                    $batch->id
                );

                if (!$pdfContent || strlen($pdfContent) < 100) {
                    throw new \Exception("Generated PDF empty for {$form->form_code}");
                }

                $filePath = '';

                /*
            |--------------------------------------------------------------------------
            | 3️⃣ FULL SUBSCRIPTION PERSISTENCE
            |--------------------------------------------------------------------------
            */

                if ($isFull) {

                    $directory = "generated_forms/{$tenantId}/{$batch->id}";
                    Storage::disk('local')->makeDirectory($directory);

                    $fileName = "{$form->form_code}.pdf";
                    $filePath = "{$directory}/{$fileName}";

                    Storage::disk('local')->put($filePath, $pdfContent);

                    if (!Storage::disk('local')->exists($filePath)) {
                        throw new \Exception("Failed writing file {$fileName}");
                    }

                    \App\Models\ComplianceBatchForm::create([
                        'tenant_id' => $tenantId,
                        'batch_id'  => $batch->id,
                        'form_code' => $form->form_code,
                        'section'   => $form->section->section_name ?? 'General',
                        'file_path' => $filePath,
                        'status'    => 'success',
                        'created_at' => now(),
                    ]);
                }

                $results[$formId] = [
                    'success' => true,
                    'form_code' => $form->form_code,
                    'file_path' => $filePath,
                ];
            } catch (\Exception $e) {

                $results[$formId] = [
                    'success' => false,
                    'form_code' => $form->form_code ?? 'UNKNOWN',
                    'error' => $e->getMessage(),
                ];
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ FINAL STATUS RESOLUTION
    |--------------------------------------------------------------------------
    */

        $successCount = collect($results)->where('success', true)->count();
        $totalCount = count($results);

        if ($successCount === 0) {
            $finalStatus = 'failed';
        } elseif ($successCount < $totalCount) {
            $finalStatus = 'partially_completed';
        } else {
            $finalStatus = 'completed';
        }

        $batch->update([
            'status' => $finalStatus,
            'processed_at' => now(),
            'results' => $results,
        ]);

        /*
    |--------------------------------------------------------------------------
    | 5️⃣ HARD VALIDATION FOR FULL
    |--------------------------------------------------------------------------
    */

        if ($isFull) {
            $persistedCount = \App\Models\ComplianceBatchForm::where('batch_id', $batch->id)->count();

            if ($persistedCount === 0) {
                throw new \Exception("No forms persisted for batch {$batch->id}");
            }

            logger("Batch {$batch->id} persisted forms: {$persistedCount}");
        }

        return $results;
    }
}
