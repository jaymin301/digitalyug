<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add lead-equivalent fields to the projects table so projects can be
     * created directly (without a lead) while remaining fully self-contained.
     *
     * Columns deliberately excluded from this migration:
     *   - start_date / end_date  →  managed by the existing activation flow
     *   - lead_status            →  not needed; all converted leads are tracked
     *
     * backward-compatible: all new columns are nullable or carry a default.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            // Make lead_id nullable so projects can be created without a lead
            $table->foreignId('lead_id')->nullable()->change();

            // ── Client / Agency info ───────────────────────────────
            $table->date('date')->nullable()->after('lead_id')
                ->comment('Lead-equivalent enquiry date');

            $table->string('day', 10)->nullable()->after('date')
                ->comment('Auto-filled day name from date (Monday, Tuesday…)');

            $table->foreignId('agency_id')->nullable()->after('day')
                ->constrained('agencies')->onDelete('set null')
                ->comment('Agency associated with this project');

            $table->string('customer_name')->nullable()->after('agency_id')
                ->comment('Client / customer display name');

            $table->string('contact_number', 20)->nullable()->after('customer_name')
                ->comment('Primary contact number for the client');

            // ── Content counts ─────────────────────────────────────
            $table->unsignedInteger('total_reels')->default(0)->after('contact_number')
                ->comment('Total reels agreed for this project');

            $table->unsignedInteger('total_posts')->default(0)->after('total_reels')
                ->comment('Total static posts agreed for this project');

            // ── Budget distribution ────────────────────────────────
            $table->decimal('total_meta_budget', 12, 2)->default(0)->after('total_posts')
                ->comment('Combined meta ad budget for this project');

            $table->decimal('client_meta_budget', 12, 2)->default(0)->after('total_meta_budget')
                ->comment('Portion of meta budget borne by the client');

            $table->decimal('dy_meta_budget', 12, 2)->default(0)->after('client_meta_budget')
                ->comment('Portion of meta budget retained by Digital Yug');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropColumn([
                'date', 'day', 'agency_id',
                'customer_name', 'contact_number',
                'total_reels', 'total_posts',
                'total_meta_budget', 'client_meta_budget', 'dy_meta_budget',
            ]);

            // Revert lead_id back to non-nullable (only safe on a fresh DB)
            $table->foreignId('lead_id')->nullable(false)->change();
        });
    }
};
