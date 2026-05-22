<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('compliance_generation_logs', function (Blueprint $table) {

            if (!Schema::hasColumn('compliance_generation_logs', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable();
            }

            if (!Schema::hasColumn('compliance_generation_logs', 'form_code')) {
                $table->string('form_code');
            }

            if (!Schema::hasColumn('compliance_generation_logs', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id');
            }

            if (!Schema::hasColumn('compliance_generation_logs', 'status')) {
                $table->string('status')->default('pending');
            }

            if (!Schema::hasColumn('compliance_generation_logs', 'generated_file_path')) {
                $table->string('generated_file_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
