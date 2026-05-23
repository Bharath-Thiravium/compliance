<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('compliance_forms_master')
            ->where('form_code', 'EPFInspection')
            ->update([
                'is_active'  => 0,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('compliance_forms_master')
            ->where('form_code', 'EPFInspection')
            ->update([
                'is_active'  => 1,
                'updated_at' => now(),
            ]);
    }
};
