<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('deduction_date');
            $table->string('particulars')->nullable()->comment('Particulars of damage or loss');
            $table->boolean('showed_cause')->default(false)->comment('Whether workmen showed cause');
            $table->string('witness_name')->nullable()->comment('Name of witness');
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('num_instalments')->nullable();
            $table->string('first_month')->nullable();
            $table->string('last_month')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
            
            $table->index(['tenant_id', 'branch_id', 'deduction_date']);
            $table->index(['employee_id', 'deduction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_deductions');
    }
};
