<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('edit_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('concept_id')->nullable()->constrained('concepts')->onDelete('set null');
            $table->foreignId('shoot_schedule_id')->nullable()->constrained('shoot_schedules')->onDelete('set null');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade'); // video editor
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // manager
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('total_videos')->default(1);
            $table->unsignedInteger('completed_count')->default(0);
            $table->enum('status', ['pending', 'in_progress', 'review', 'approved', 'revision'])->default('pending');
            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_tasks');
    }
};
