<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_labour', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('contractor_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('deployment_location')->nullable();
            $table->decimal('wage_rate', 12, 2);
            $table->date('employment_start');
            $table->date('employment_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
            
            $table->index(['tenant_id', 'contractor_id']);
            $table->index(['contractor_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_labour');
    }
};
