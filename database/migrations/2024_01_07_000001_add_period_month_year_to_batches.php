<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_execution_batches', function (Blueprint $table) {

            if (!Schema::hasColumn('compliance_execution_batches', 'period_month')) {
                $table->integer('period_month')->nullable()->after('section_id');
            }

            if (!Schema::hasColumn('compliance_execution_batches', 'period_year')) {
                $table->integer('period_year')->nullable()->after('period_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compliance_execution_batches', function (Blueprint $table) {
            $table->dropColumn(['period_month', 'period_year']);
        });
    }
};
