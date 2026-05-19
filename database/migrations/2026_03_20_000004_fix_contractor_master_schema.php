<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contractor_master')) {
            Schema::table('contractor_master', function (Blueprint $table) {
                if (!Schema::hasColumn('contractor_master', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('contractor_master', 'contractor_code')) {
                    $table->string('contractor_code')->nullable()->after('company_name');
                }
                if (!Schema::hasColumn('contractor_master', 'contractor_name')) {
                    $table->string('contractor_name')->nullable()->after('contractor_code');
                }
                if (!Schema::hasColumn('contractor_master', 'address')) {
                    $table->text('address')->nullable()->after('company_address');
                }
                if (!Schema::hasColumn('contractor_master', 'phone')) {
                    $table->string('phone')->nullable()->after('contact_number');
                }
                if (!Schema::hasColumn('contractor_master', 'license_no')) {
                    $table->string('license_no')->nullable();
                }
                if (!Schema::hasColumn('contractor_master', 'license_expiry')) {
                    $table->date('license_expiry')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contractor_master')) {
            Schema::table('contractor_master', function (Blueprint $table) {
                $columns = ['branch_id', 'contractor_code', 'contractor_name', 'address', 'phone', 'license_no', 'license_expiry'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('contractor_master', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
