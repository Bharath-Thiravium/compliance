<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->enum('incident_type', ['accident', 'serious', 'dangerous', 'esi'])->index();
            $table->date('incident_date')->index();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('authority_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('document_path');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_documents');
    }
};
