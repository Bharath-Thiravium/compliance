<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->enum('inspection_type', ['epf', 'esi', 'labour', 'factory'])->index();
            $table->date('inspection_date')->index();
            $table->string('inspecting_authority')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('document_path');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_documents');
    }
};
