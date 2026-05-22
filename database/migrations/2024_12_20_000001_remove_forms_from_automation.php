<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable automation for all forms first
        DB::table('compliance_forms_master')->update(['auto_generate' => true]);

        // Then disable the three excluded forms
        DB::table('compliance_forms_master')
            ->whereIn('form_code', ['Form8', 'HazardReg', 'ShopsForm13'])
            ->update(['auto_generate' => false]);
    }

    public function down(): void
    {
        DB::table('compliance_forms_master')
            ->whereIn('form_code', ['Form8', 'HazardReg', 'ShopsForm13'])
            ->update(['auto_generate' => true]);
    }
};
