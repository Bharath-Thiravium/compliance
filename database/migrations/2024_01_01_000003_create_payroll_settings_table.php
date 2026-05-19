<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->decimal('pf_rate', 5, 2)->default(12.00);
            $table->decimal('esi_rate', 5, 2)->default(0.75);
            $table->decimal('ot_multiplier', 5, 2)->default(2.00);
            $table->decimal('bonus_min_percent', 5, 2)->default(8.33);
            $table->decimal('bonus_max_percent', 5, 2)->default(20.00);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
