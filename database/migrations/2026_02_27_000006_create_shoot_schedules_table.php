<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shoot_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('location');
            $table->date('shoot_date');
            $table->time('planned_start_time')->nullable();
            $table->datetime('checkin_at')->nullable();
            $table->datetime('checkout_at')->nullable();
            $table->foreignId('shooting_person_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('model_name')->nullable();
            $table->foreignId('concept_writer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('helper_name')->nullable();
            $table->unsignedInteger('reels_shot')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoot_schedules');
    }
};
