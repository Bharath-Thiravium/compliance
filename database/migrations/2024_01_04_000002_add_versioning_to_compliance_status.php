<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_status', function (Blueprint $table) {
            $table->integer('version_number')->default(1)->after('status');
            $table->boolean('is_revised')->default(false)->after('version_number');
            $table->unsignedBigInteger('revised_from_id')->nullable()->after('is_revised');
            $table->text('revision_reason')->nullable()->after('revised_from_id');

            $table->foreign('revised_from_id')->references('id')->on('compliance_status')->onDelete('set null');
            $table->index('revised_from_id');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_status', function (Blueprint $table) {
            $table->dropForeign(['revised_from_id']);
            $table->dropIndex(['revised_from_id']);
            $table->dropColumn(['version_number', 'is_revised', 'revised_from_id', 'revision_reason']);
        });
    }
};
