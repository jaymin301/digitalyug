<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_task_id')->constrained('concept_tasks')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('client_allocation')->nullable(); // e.g., "Reel 1"
            $table->text('remarks')->nullable(); // per-concept remarks from manager
            $table->text('writer_notes')->nullable(); // concept writer's notes
            $table->enum('status', ['draft', 'client_review', 'approved', 'rejected'])->default('draft');
            $table->text('adjustment_suggestion')->nullable(); // shooting person suggestion
            $table->unsignedInteger('sequence')->default(1); // ordering
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concepts');
    }
};
