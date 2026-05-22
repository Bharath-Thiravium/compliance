<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_labour_deployment', 'overtime_hours')) {
                $table->decimal('overtime_hours', 8, 2)->default(0)->after('wage_rate');
            }
            if (!Schema::hasColumn('contract_labour_deployment', 'overtime_wages')) {
                $table->decimal('overtime_wages', 12, 2)->default(0)->after('overtime_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            $table->dropColumn(['overtime_hours', 'overtime_wages']);
        });
    }
};
