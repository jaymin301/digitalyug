<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id', 'name', 'start_date', 'end_date', 'stage',
        'total_concepts', 'approved_concepts',
        'total_shoots', 'completed_shoots',
        'total_edits', 'completed_edits',
        'manager_id', 'notes',
        // Lead-equivalent fields (stored on project for self-contained records)
        'date', 'day', 'agency_id',
        'customer_name', 'contact_number',
        'total_reels', 'total_posts',
        'total_meta_budget', 'client_meta_budget', 'dy_meta_budget',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'date'               => 'date',
        'total_meta_budget'  => 'decimal:2',
        'client_meta_budget' => 'decimal:2',
        'dy_meta_budget'     => 'decimal:2',
    ];

    protected $appends = [
        'approved_concepts_count',
        'completed_shoots_count',
        'completed_edits_count',
        'progress_percent'
    ];

    // Auto-compute derived values before saving
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($project) {
            // Auto-fill day name from date
            if ($project->date) {
                $project->day = Carbon::parse($project->date)->format('l');
            }
            // Auto-set end_date = start_date + 1 month
            if ($project->start_date && !$project->isDirty('end_date')) {
                $project->end_date = Carbon::parse($project->start_date)->addMonth();
            }
        });
    }

    // ── Relationships ─────────────────────────────────────
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function conceptTasks()
    {
        return $this->hasMany(ConceptTask::class);
    }

    public function concepts()
    {
        return $this->hasMany(Concept::class);
    }

    public function approvedConcepts()
    {
        return $this->hasMany(Concept::class)->where('status', 'approved');
    }

    public function shootSchedules()
    {
        return $this->hasMany(ShootSchedule::class);
    }

    public function editTasks()
    {
        return $this->hasMany(EditTask::class);
    }

    // ── Accessors ─────────────────────────────────────────
    public function getStageBadgeAttribute(): string
    {
        return match ($this->stage) {
            'pending'   => '<span class="badge bg-secondary">Pending</span>',
            'concept'   => '<span class="badge bg-info">Concept</span>',
            'shooting'  => '<span class="badge bg-warning text-dark">Shooting</span>',
            'editing'   => '<span class="badge bg-primary">Editing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getApprovedConceptsCountAttribute(): int
    {
        return $this->concepts()->where('status', 'approved')->count();
    }

    public function getCompletedShootsCountAttribute(): int
    {
        return $this->shootSchedules()->where('status', 'completed')->count();
    }

    public function getCompletedEditsCountAttribute(): int
    {
        // Use the newly introduced approved_videos count for accurate pipeline progress
        return $this->editTasks()->sum('approved_videos');
    }

    public function getProgressPercentAttribute(): int
    {
        // Use own total_reels first; fall back to lead's total_reels for legacy lead-based records
        $targetReels = $this->total_reels ?: ($this->lead?->total_reels ?? 1) ?: 1;

        $totalConceptsTarget = $this->conceptTasks()->sum('concepts_required') ?: $targetReels;
        $totalEditsTarget    = $this->editTasks()->sum('total_videos') ?: $targetReels;

        // Stage-wise weightage:
        // Concepts: 30%, Shooting: 35%, Editing: 35%
        $conceptProgress = ($this->approved_concepts_count / $totalConceptsTarget) * 30;

        $shootCount    = $this->shootSchedules()->count();
        $shootProgress = ($shootCount > 0)
            ? ($this->completed_shoots_count / $shootCount) * 35
            : 0;

        $editProgress = ($this->completed_edits_count / $totalEditsTarget) * 35;

        $totalProgress = $conceptProgress + $shootProgress + $editProgress;

        if ($this->stage === 'completed') return 100;
        if ($this->stage === 'pending')   return 2;

        return (int) min(100, round($totalProgress));
    }
}
