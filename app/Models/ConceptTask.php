<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConceptTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'assigned_to', 'assigned_by',
        'concepts_required', 'general_remarks', 'status', 'due_date','client_token','client_token_expires_at'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'client_token_expires_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class , 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class , 'assigned_by');
    }

    public function concepts()
    {
        return $this->hasMany(Concept::class,'concept_task_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
                'pending' => '<span class="badge bg-secondary">Pending</span>',
                'in_progress' => '<span class="badge bg-warning text-dark">In Progress</span>',
                'submitted' => '<span class="badge bg-info">Submitted</span>',
                'completed' => '<span class="badge bg-success">Completed</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }
}
