<?php

namespace App\Services;

use App\Models\ComplianceFormsMaster;
use Illuminate\Support\Collection;

class GovernmentFormUpdateService
{
    public function getActiveUpdates(int $limit = 10): Collection
    {
        return ComplianceFormsMaster::with('section')
            ->where('is_active', true)
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(function ($form) {
                return (object) [
                    'form_code'          => $form->form_code,
                    'form_name'          => $form->form_name,
                    'source_department'  => $form->department_name ?? null,
                    'source_domain'      => null,
                    'update_message'     => $form->change_summary ?? null,
                    'published_on'       => $form->effective_date,
                    'source_url'         => $form->source_url ?? null,
                    'source_type'        => $form->act_type ?? null,
                    'official_status'    => $form->is_active ? 'Active' : 'Inactive',
                    'section'            => optional($form->section)->section_name,
                    'status'             => $form->is_active ? 'Active' : 'Inactive',
                    'updated_at'         => $form->updated_at ? $form->updated_at->diffForHumans() : null,
                    'is_new'             => $form->updated_at && $form->updated_at->gte(now()->subDays(7)),
                ];
            });
    }
}
