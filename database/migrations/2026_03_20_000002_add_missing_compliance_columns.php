<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_labour_deployment', 'nature_of_work')) {
                $table->string('nature_of_work')->nullable()->after('work_order_number');
            }
            if (!Schema::hasColumn('contract_labour_deployment', 'work_location')) {
                $table->string('work_location')->nullable()->after('nature_of_work');
            }
            if (!Schema::hasColumn('contract_labour_deployment', 'termination_reason')) {
                $table->string('termination_reason')->nullable()->after('work_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            $table->dropColumn(['nature_of_work', 'work_location', 'termination_reason']);
        });
    }
};
