<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Concept extends Model
{
    use HasFactory;

    protected $fillable = [
        'concept_task_id', 'project_id', 'title', 'description',
        'client_allocation', 'remarks', 'writer_notes',
        'status', 'adjustment_suggestion', 'sequence','shoot_id','is_review_reel'
    ];

    // ── Relationships ─────────────────────────────────────
    public function conceptTask()
    {
        return $this->belongsTo(ConceptTask::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function shootConceptLinks()
    {
        return $this->hasMany(ShootConceptLink::class);
    }

    public function editTasks()
    {
        return $this->hasMany(EditTask::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function shootingConcept()
    {
        return $this->belongsTo(ShootSchedule::class, 'shoot_id');
    }
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
                'draft' => '<span class="badge bg-secondary">Draft</span>',
                'client_review' => '<span class="badge bg-warning text-dark">Client Review</span>',
                'approved' => '<span class="badge bg-success">Approved</span>',
                'rejected' => '<span class="badge bg-danger">Rejected</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }
}
