<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'day', 'agency_name', 'customer_name', 'contact_number',
        'total_reels', 'total_posts', 'total_meta_budget',
        'client_meta_budget', 'dy_meta_budget', 'notes',
        'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_meta_budget' => 'decimal:2',
        'client_meta_budget' => 'decimal:2',
        'dy_meta_budget' => 'decimal:2',
    ];

    // Auto-set day from date on save
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($lead) {
            if ($lead->date) {
                $lead->day = Carbon::parse($lead->date)->format('l');
            }
        });
    }

    // ── Relationships ─────────────────────────────────────
    public function createdBy()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class , 'updated_by');
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    // ── Scopes ────────────────────────────────────────────
    public function scopeNew($q)
    {
        return $q->where('status', 'new');
    }
    public function scopeConverted($q)
    {
        return $q->where('status', 'converted');
    }

    // ── Accessors ─────────────────────────────────────────
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
                'new' => '<span class="badge bg-info">New</span>',
                'contacted' => '<span class="badge bg-warning text-dark">Contacted</span>',
                'confirmed' => '<span class="badge bg-primary">Confirmed</span>',
                'converted' => '<span class="badge bg-success">Converted</span>',
                'lost' => '<span class="badge bg-danger">Lost</span>',
                default => '<span class="badge bg-secondary">Unknown</span>',
            };
    }
}
