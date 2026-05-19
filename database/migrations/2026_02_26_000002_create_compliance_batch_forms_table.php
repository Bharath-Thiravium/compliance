<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_batch_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('batch_id');
            $table->string('form_code');
            $table->string('section');
            $table->string('file_path');
            $table->string('status')->default('success');
            $table->timestamp('created_at');

            $table->index(['batch_id', 'status']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_batch_forms');
    }
};
