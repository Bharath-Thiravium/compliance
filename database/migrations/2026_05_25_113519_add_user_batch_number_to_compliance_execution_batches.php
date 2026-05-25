<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_execution_batches', function (Blueprint $table) {
            $table->unsignedInteger('user_batch_number')->nullable()->after('created_by');
        });

        // Backfill existing rows: number per tenant
        $rows = DB::table('compliance_execution_batches')
            ->orderBy('tenant_id')
            ->orderBy('id')
            ->get(['id', 'tenant_id']);

        $counters = [];
        foreach ($rows as $row) {
            $key = $row->tenant_id;
            $counters[$key] = ($counters[$key] ?? 0) + 1;
            DB::table('compliance_execution_batches')
                ->where('id', $row->id)
                ->update(['user_batch_number' => $counters[$key]]);
        }
    }

    public function down(): void
    {
        Schema::table('compliance_execution_batches', function (Blueprint $table) {
            $table->dropColumn('user_batch_number');
        });
    }
};
