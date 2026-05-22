<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workforce_fines')) {
            Schema::create('workforce_fines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('employee_id');
                $table->date('fine_date');
                $table->string('reason')->nullable()->comment('Reason for fine');
                $table->decimal('amount', 12, 2)->default(0);
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
                
                $table->index(['tenant_id', 'branch_id', 'fine_date']);
                $table->index(['employee_id', 'fine_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_fines');
    }
};
