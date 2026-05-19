<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('establishment_name')->nullable();
            $table->string('factory_license_no')->nullable();
            $table->string('pf_code')->nullable();
            $table->string('esi_code')->nullable();
            $table->string('labour_office_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['establishment_name', 'factory_license_no', 'pf_code', 'esi_code', 'labour_office_address']);
        });
    }
};
