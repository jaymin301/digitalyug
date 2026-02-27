<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShootSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'location', 'shoot_date', 'planned_start_time',
        'checkin_at', 'checkout_at',
        'shooting_person_id', 'model_name', 'concept_writer_id',
        'helper_name', 'reels_shot', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'shoot_date' => 'date',
        'checkin_at' => 'datetime',
        'checkout_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function shootingPerson()
    {
        return $this->belongsTo(User::class , 'shooting_person_id');
    }

    public function conceptWriter()
    {
        return $this->belongsTo(User::class , 'concept_writer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    public function conceptLinks()
    {
        return $this->hasMany(ShootConceptLink::class);
    }

    public function concepts()
    {
        return $this->belongsToMany(Concept::class , 'shoot_concept_links')
            ->withPivot('is_shot')
            ->withTimestamps();
    }

    public function editTasks()
    {
        return $this->hasMany(EditTask::class);
    }

    // ── Accessors ─────────────────────────────────────────
    public function getDurationAttribute(): ?string
    {
        if ($this->checkin_at && $this->checkout_at) {
            $diff = $this->checkin_at->diff($this->checkout_at);
            return $diff->format('%Hh %Im');
        }
        return null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
                'scheduled' => '<span class="badge bg-info">Scheduled</span>',
                'in_progress' => '<span class="badge bg-warning text-dark">In Progress</span>',
                'completed' => '<span class="badge bg-success">Completed</span>',
                'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }
}
