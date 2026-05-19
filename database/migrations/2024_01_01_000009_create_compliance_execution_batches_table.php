<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_execution_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('section_id');
            $table->date('period_from');
            $table->date('period_to');
            $table->integer('period_month')->nullable();
            $table->integer('period_year')->nullable();
            $table->json('form_ids');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('results')->nullable();
            $table->string('generated_report_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_execution_batches');
    }
};
