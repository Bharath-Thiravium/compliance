<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_manual_batch_items', function (Blueprint $table) {
            // Fix status enum: 'uploaded' → 'completed', add compliance_result
            $table->enum('status', ['pending', 'completed', 'skipped'])
                  ->default('pending')
                  ->change();

            $table->enum('compliance_result', ['compliant', 'not_applicable'])
                  ->nullable()
                  ->after('status');

            $table->timestamp('uploaded_at')->nullable()->after('document_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete()->after('uploaded_at');
            $table->unsignedBigInteger('file_size')->nullable()->after('uploaded_by'); // bytes

            // Composite indexes for multi-tenant scoped queries
            $table->index(['tenant_id', 'branch_id', 'batch_id'], 'idx_manual_items_tenant_branch_batch');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_manual_batch_items', function (Blueprint $table) {
            $table->dropIndex('idx_manual_items_tenant_branch_batch');
            $table->dropConstrainedForeignId('uploaded_by');
            $table->dropColumn(['compliance_result', 'uploaded_at', 'file_size']);
            $table->enum('status', ['pending', 'uploaded', 'skipped'])->default('pending')->change();
        });
    }
};
