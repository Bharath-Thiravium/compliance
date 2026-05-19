<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->date('attendance_date')->index();
            $table->enum('status', ['present', 'absent', 'leave', 'holiday'])->default('present');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');

            $table->unique(['tenant_id', 'employee_id', 'attendance_date'], 'att_unique');
            $table->index(['tenant_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_attendance');
    }
};
