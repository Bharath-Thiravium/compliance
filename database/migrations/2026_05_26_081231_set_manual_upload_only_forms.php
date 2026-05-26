<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $manualForms = [
        'CLRA_LICENSE',
        'CLRA_RETURN',
        'CONTRACTOR_MASTER',
        'FORM_XXIV',
        'FORM_XXV',
        'SHOPS_FORM_1',
    ];

    public function up(): void
    {
        DB::table('compliance_forms_master')
            ->whereIn('form_code', $this->manualForms)
            ->update(['upload_only' => 1, 'auto_generate' => 0]);
    }

    public function down(): void
    {
        DB::table('compliance_forms_master')
            ->whereIn('form_code', $this->manualForms)
            ->update(['upload_only' => 0]);
    }
};
