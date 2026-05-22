<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_manual_batch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('compliance_id');
            $table->enum('status', ['pending', 'uploaded', 'skipped'])->default('pending');
            $table->string('document_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('compliance_execution_batches')->onDelete('cascade');
            $table->foreign('compliance_id')->references('id')->on('compliance_manual_master')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_manual_batch_items');
    }
};
