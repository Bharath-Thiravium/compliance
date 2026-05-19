<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_register', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->date('hazard_date');
            $table->string('hazard_type');
            $table->text('description');
            $table->string('location');
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('open'); // open, closed, mitigated
            $table->text('corrective_action')->nullable();
            $table->date('action_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_register');
    }
};
