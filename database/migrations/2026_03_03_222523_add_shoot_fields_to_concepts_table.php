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
        Schema::table('concepts', function (Blueprint $table) {
            $table->foreignId('shoot_id')->nullable()->constrained('shoot_schedules')->nullOnDelete()->after('sequence');
            $table->boolean('is_review_reel')->default(false)->after('shoot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concepts', function (Blueprint $table) {
            //
        });
    }
};
