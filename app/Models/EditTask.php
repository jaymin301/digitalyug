<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EditTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'shoot_schedule_id',
        'assigned_to', 'assigned_by', 'title', 'description',
        'total_videos', 'completed_count', 'approved_videos', 'status',
        'approval_notes', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // public function concepts()
    // {
    //     return $this->belongsToMany(Concept::class , 'edit_task_concept');
    // }

    public function concepts()
    {
        return $this->belongsToMany(Concept::class, 'edit_task_concept')
                    ->using(EditTaskConcept::class)
                    ->withTimestamps();
    }

    public function shootSchedule()
    {
        return $this->belongsTo(ShootSchedule::class , 'shoot_schedule_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class , 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class , 'assigned_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class , 'approved_by');
    }

    // ── Accessors ─────────────────────────────────────────
    public function getProgressPercentAttribute(): int
    {
        if ($this->total_videos == 0)
            return 0;
        // Progress is now strictly based on final approved videos
        return (int)round(($this->approved_videos / $this->total_videos) * 100);
    }

    public function videoEntries()
    {
        return $this->hasMany(EditTaskVideo::class);
    }
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
                'pending' => '<span class="badge" style="background:rgba(100,116,139,0.1);color:#64748b;border:1px solid rgba(100,116,139,0.2);"><i class="fa-solid fa-clock-rotate-left me-1"></i>Pending</span>',
                'in_progress' => '<span class="badge" style="background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.2);"><i class="fa-solid fa-person-digging me-1"></i>In Progress</span>',
                'review' => '<span class="badge" style="background:rgba(14,165,233,0.1);color:#0284c7;border:1px solid rgba(14,165,233,0.2);"><i class="fa-solid fa-magnifying-glass me-1"></i>In Review</span>',
                'approved' => '<span class="badge" style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.2);"><i class="fa-solid fa-circle-check me-1"></i>Approved</span>',
                'revision' => '<span class="badge" style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.2);"><i class="fa-solid fa-arrows-rotate me-1"></i>Revision</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }
}
