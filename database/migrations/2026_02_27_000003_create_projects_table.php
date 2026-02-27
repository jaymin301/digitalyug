<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('stage', ['pending', 'concept', 'shooting', 'editing', 'completed'])->default('pending');
            $table->unsignedInteger('total_concepts')->default(0);
            $table->unsignedInteger('approved_concepts')->default(0);
            $table->unsignedInteger('total_shoots')->default(0);
            $table->unsignedInteger('completed_shoots')->default(0);
            $table->unsignedInteger('total_edits')->default(0);
            $table->unsignedInteger('completed_edits')->default(0);
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
