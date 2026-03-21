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
            if (Schema::hasColumn('edit_task_videos', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('edit_task_videos', 'editing_requirements')) {
                $table->dropColumn('editing_requirements');
            }
            if (!Schema::hasColumn('edit_task_videos', 'editor_feedback')) {
                $table->text('editor_feedback')->nullable()->after('admin_status');
            }
            if (!Schema::hasColumn('edit_task_videos', 'admin_internal_note')) {
                $table->text('admin_internal_note')->nullable()->after('editor_feedback');
            }
        });
    }

    public function down(): void
    {
        Schema::table('edit_task_videos', function (Blueprint $table) {
            if (!Schema::hasColumn('edit_task_videos', 'notes')) {
                $table->text('notes')->nullable();
            }
            $table->dropColumn(['editor_feedback', 'admin_internal_note']);
        });
    }
};
