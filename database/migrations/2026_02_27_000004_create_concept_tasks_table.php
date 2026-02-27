<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('concept_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade'); // concept writer
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // manager
            $table->unsignedInteger('concepts_required')->default(1);
            $table->text('general_remarks')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'submitted', 'completed'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concept_tasks');
    }
};
