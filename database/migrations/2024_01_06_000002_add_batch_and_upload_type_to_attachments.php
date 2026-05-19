<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->nullable()->after('form_id');
            $table->enum('upload_type', ['manual', 'automated'])->default('automated')->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_attachments', function (Blueprint $table) {
            $table->dropColumn(['batch_id', 'upload_type']);
        });
    }
};
