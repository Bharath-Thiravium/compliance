<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_employee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('employee_code');
            $table->string('name');
            $table->string('pf_number')->nullable();
            $table->string('esi_number')->nullable();
            $table->date('date_of_joining');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'employee_code'], 'workforce_employee_tenant_employee_code_unique');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_employee');
    }
};
