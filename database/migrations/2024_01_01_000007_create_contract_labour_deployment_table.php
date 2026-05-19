<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contract_labour_deployment')) {
            Schema::create('contract_labour_deployment', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('contractor_id')->nullable()->index();
                $table->unsignedBigInteger('contractor_compliance_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->decimal('wage_rate', 12, 2);
                $table->date('deployment_start');
                $table->date('deployment_end')->nullable();
                $table->string('work_order_number')->nullable();
                $table->date('work_order_date')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
                
                $table->index(['tenant_id', 'contractor_id']);
                $table->index(['contractor_id', 'employee_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_labour_deployment');
    }
};
