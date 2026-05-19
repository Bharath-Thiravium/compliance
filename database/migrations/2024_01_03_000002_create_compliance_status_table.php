<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('form_id')->index();
            $table->date('period_from');
            $table->date('period_to');
            $table->enum('status', ['Pending', 'Generated', 'Uploaded', 'NIL', 'Locked'])->default('Pending');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'branch_id', 'form_id', 'period_from', 'period_to'], 'unique_compliance_period');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('compliance_forms_master')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_status');
    }
};
