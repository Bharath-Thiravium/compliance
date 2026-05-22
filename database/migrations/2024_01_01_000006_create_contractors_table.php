<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('contractor_name');
            $table->string('license_number')->index();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->integer('max_worker_limit');
            $table->string('pf_code')->nullable();
            $table->string('esi_code')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'license_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
