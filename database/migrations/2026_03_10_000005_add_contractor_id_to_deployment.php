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
                // Only add if not already present
                if (!Schema::hasColumn('contract_labour_deployment', 'contractor_compliance_id')) {
                    $table->unsignedBigInteger('contractor_compliance_id')->nullable()->after('contractor_id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                if (Schema::hasColumn('contract_labour_deployment', 'contractor_compliance_id')) {
                    // Drop foreign key if it exists
                    try {
                        $table->dropForeign(['contractor_compliance_id']);
                    } catch (\Exception $e) {
                        // Foreign key doesn't exist, continue
                    }
                    $table->dropColumn('contractor_compliance_id');
                }
            });
        }
    }
};
