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
        Schema::table('edit_task_videos', function (Blueprint $table) {
            $table->enum('admin_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->foreignId('admin_reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_status');
            $table->timestamp('admin_reviewed_at')->nullable()->after('admin_reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edit_task_videos', function (Blueprint $table) {
            $table->dropForeign(['admin_reviewed_by']);
            $table->dropColumn(['admin_status', 'admin_reviewed_by', 'admin_reviewed_at']);
        });
    }
};
