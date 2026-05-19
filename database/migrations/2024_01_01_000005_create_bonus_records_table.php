<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('financial_year')->index();
            $table->decimal('bonus_percentage', 5, 2);
            $table->decimal('bonus_amount', 12, 2);
            $table->date('payment_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
            
            $table->index(['tenant_id', 'branch_id', 'financial_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_records');
    }
};
