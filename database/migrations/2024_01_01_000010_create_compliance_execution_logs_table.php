<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('batch_id')->index();
            $table->string('form_code');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'preview'])->default('pending');
            $table->integer('execution_time')->nullable()->comment('milliseconds');
            $table->integer('records_generated')->default(0);
            $table->text('error_message')->nullable();
            $table->string('execution_mode')->default('batch')->comment('preview, pdf, batch');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('compliance_execution_batches')->onDelete('cascade');
            
            $table->index(['tenant_id', 'batch_id']);
            $table->index(['batch_id', 'form_code']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_execution_logs');
    }
};
