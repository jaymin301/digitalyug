<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shoot_concept_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoot_schedule_id')->constrained('shoot_schedules')->onDelete('cascade');
            $table->foreignId('concept_id')->constrained('concepts')->onDelete('cascade');
            $table->boolean('is_shot')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoot_concept_links');
    }
};
