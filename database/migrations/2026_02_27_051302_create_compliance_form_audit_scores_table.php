<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compliance_form_audit_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('batch_id');
            $table->string('form_code', 50);
            $table->integer('audit_score')->default(0);
            $table->string('status', 20)->default('pending');
            $table->json('violations')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'batch_id']);
            $table->index('form_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_form_audit_scores');
    }
};
