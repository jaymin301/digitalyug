<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('panel_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // recipient
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type'); // concept_assigned, shoot_scheduled, edit_assigned, concept_approved, etc.
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable(); // URL to the relevant item
            $table->boolean('is_read')->default(false);
            $table->nullableMorphs('notifiable'); // polymorphic: Lead, Project, ConceptTask, ShootSchedule, EditTask
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_notifications');
    }
};
