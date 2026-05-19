<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('form_code')->index();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->enum('frequency_type', ['monthly', 'annual', 'event', 'inspection']);
            $table->string('file_path');
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'form_code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_forms');
    }
};
