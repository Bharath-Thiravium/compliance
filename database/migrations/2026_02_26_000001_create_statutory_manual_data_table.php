<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statutory_manual_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->integer('month');
            $table->integer('year');
            $table->json('establishment_details')->nullable();
            $table->json('employer_details')->nullable();
            $table->json('employee_summary')->nullable();
            $table->json('wage_summary')->nullable();
            $table->json('attendance_summary')->nullable();
            $table->json('accident_details')->nullable();
            $table->json('contractor_summary')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'month', 'year']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statutory_manual_data');
    }
};
