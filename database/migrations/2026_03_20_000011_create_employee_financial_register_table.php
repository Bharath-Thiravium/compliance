<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_financial_register', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('transaction_type'); // loan, fine, advance
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('reason');
            $table->string('status')->default('active'); // active, closed, pending
            $table->integer('installments')->nullable();
            $table->decimal('installment_amount', 12, 2)->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('employee_financial_register');
    }
};
