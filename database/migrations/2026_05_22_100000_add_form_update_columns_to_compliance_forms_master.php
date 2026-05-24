<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_forms_master', function (Blueprint $table) {
            if (!Schema::hasColumn('compliance_forms_master', 'change_summary')) {
                $table->text('change_summary')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('compliance_forms_master', 'effective_date')) {
                $table->date('effective_date')->nullable()->after('change_summary');
            }
            if (!Schema::hasColumn('compliance_forms_master', 'version_number')) {
                $table->string('version_number', 20)->nullable()->after('effective_date');
            }
            if (!Schema::hasColumn('compliance_forms_master', 'source_url')) {
                $table->string('source_url', 500)->nullable()->after('version_number');
            }
            if (!Schema::hasColumn('compliance_forms_master', 'department_name')) {
                $table->string('department_name', 255)->nullable()->after('source_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compliance_forms_master', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['change_summary', 'effective_date', 'version_number', 'source_url', 'department_name'],
                fn($col) => Schema::hasColumn('compliance_forms_master', $col)
            ));
        });
    }
};
