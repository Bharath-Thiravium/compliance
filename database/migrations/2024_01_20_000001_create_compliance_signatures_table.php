<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('form_code', 50);
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('signed_by_user_id');
            $table->string('signatory_name');
            $table->string('signatory_designation');
            $table->enum('signature_type', ['DRAWN', 'IMAGE', 'DIGITAL_CERT']);
            $table->string('signature_path')->nullable();
            $table->string('signature_hash', 64);
            $table->string('document_hash', 64);
            $table->string('ip_address', 45);
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->unique(['batch_id', 'form_code']);
            $table->index(['tenant_id', 'batch_id']);
            $table->index('document_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_signatures');
    }
};
