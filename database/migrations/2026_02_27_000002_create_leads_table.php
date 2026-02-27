<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('day', 10); // Monday, Tuesday, etc.
            $table->string('agency_name')->default('Digital Yug');
            $table->string('customer_name');
            $table->string('contact_number', 20);
            $table->unsignedInteger('total_reels')->default(0);
            $table->unsignedInteger('total_posts')->default(0);
            $table->decimal('total_meta_budget', 12, 2)->default(0);
            $table->decimal('client_meta_budget', 12, 2)->default(0);
            $table->decimal('dy_meta_budget', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'confirmed', 'converted', 'lost'])->default('new');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
