<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToContractLabourDeployment extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            if (!Schema::hasColumn('contract_labour_deployment', 'remarks')) {
                Schema::table('contract_labour_deployment', function (Blueprint $table) {
                    $table->text('remarks')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            if (Schema::hasColumn('contract_labour_deployment', 'remarks')) {
                Schema::table('contract_labour_deployment', function (Blueprint $table) {
                    $table->dropColumn('remarks');
                });
            }
        }
    }
}
