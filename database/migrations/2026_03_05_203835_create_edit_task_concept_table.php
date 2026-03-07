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
        Schema::create('edit_task_concept', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edit_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('concept_id')->constrained()->cascadeOnDelete();
            $table->unique(['edit_task_id', 'concept_id']);
        });

        // Drop old single concept_id column
        Schema::table('edit_tasks', function (Blueprint $table) {
            $table->dropForeign(['concept_id']);
            $table->dropColumn('concept_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edit_task_concept');

        Schema::table('edit_tasks', function (Blueprint $table) {
            $table->foreignId('concept_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
