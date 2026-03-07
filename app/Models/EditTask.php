<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EditTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'concept_id', 'shoot_schedule_id',
        'assigned_to', 'assigned_by', 'title', 'description',
        'total_videos', 'completed_count', 'status',
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

    public function concepts()
    {
        return $this->belongsToMany(Concept::class, 'edit_task_concept');
    }
    public function shootSchedule()
    {
        return $this->belongsTo(ShootSchedule::class,'shoot_schedule_id');
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
        return (int)round(($this->completed_count / $this->total_videos) * 100);
    }
    
    public function videoEntries()
    {
        return $this->hasMany(EditTaskVideo::class);
    }

    // Override completed_count to use actual video records
    public function getCompletedCountAttribute(): int
    {
        return $this->videoEntries()->where('status', 'completed')->count();
    }
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
                'pending' => '<span class="badge bg-secondary">Pending</span>',
                'in_progress' => '<span class="badge bg-warning text-dark">In Progress</span>',
                'review' => '<span class="badge bg-info">Review</span>',
                'approved' => '<span class="badge bg-success">Approved</span>',
                'revision' => '<span class="badge bg-danger">Revision</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }
}
