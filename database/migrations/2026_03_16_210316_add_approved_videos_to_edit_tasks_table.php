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
        Schema::table('edit_tasks', function (Blueprint $table) {
            $table->unsignedInteger('approved_videos')->default(0)->after('completed_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edit_tasks', function (Blueprint $table) {
            $table->dropColumn('approved_videos');
        });
    }
};
