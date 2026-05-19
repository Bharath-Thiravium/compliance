<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                if (!Schema::hasColumn('contract_labour_deployment', 'deployment_date')) {
                    $table->date('deployment_date')->nullable()->after('deployment_start');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'workmen_count')) {
                    $table->integer('workmen_count')->default(0)->after('deployment_date');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'work_description')) {
                    $table->text('work_description')->nullable()->after('workmen_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                $columns = ['deployment_date', 'workmen_count', 'work_description'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('contract_labour_deployment', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
