<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_manual_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('compliance_execution_batches')->onDelete('cascade');
            $table->string('form_code', 50);
            $table->string('file_path');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            
            $table->index(['batch_id', 'form_code']);
            $table->index(['user_id', 'form_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_manual_uploads');
    }
};
