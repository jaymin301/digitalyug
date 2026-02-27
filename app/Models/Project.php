<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id', 'name', 'start_date', 'end_date', 'stage',
        'total_concepts', 'approved_concepts',
        'total_shoots', 'completed_shoots',
        'total_edits', 'completed_edits',
        'manager_id', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Auto end_date = start_date + 1 month
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($project) {
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

    public function manager()
    {
        return $this->belongsTo(User::class , 'manager_id');
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
                'pending' => '<span class="badge bg-secondary">Pending</span>',
                'concept' => '<span class="badge bg-info">Concept</span>',
                'shooting' => '<span class="badge bg-warning text-dark">Shooting</span>',
                'editing' => '<span class="badge bg-primary">Editing</span>',
                'completed' => '<span class="badge bg-success">Completed</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }

    public function getProgressPercentAttribute(): int
    {
        $stages = ['pending' => 0, 'concept' => 25, 'shooting' => 50, 'editing' => 75, 'completed' => 100];
        return $stages[$this->stage] ?? 0;
    }
}
