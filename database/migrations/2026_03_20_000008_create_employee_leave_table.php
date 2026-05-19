<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('leave_from');
            $table->date('leave_to');
            $table->string('leave_type'); // Casual, Earned, Sick, etc.
            $table->integer('days');
            $table->text('reason')->nullable();
            $table->string('status')->default('approved'); // approved, pending, rejected
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave');
    }
};
