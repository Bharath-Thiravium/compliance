<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('branch_name');
            $table->string('unit_name')->nullable();
            $table->string('factory_license_number')->nullable();
            $table->text('address')->nullable();
            $table->string('pf_code')->nullable();
            $table->string('esi_code')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'branch_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
