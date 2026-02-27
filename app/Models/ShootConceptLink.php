<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShootConceptLink extends Model
{
    protected $fillable = ['shoot_schedule_id', 'concept_id', 'is_shot'];

    protected $casts = ['is_shot' => 'boolean'];

    public function shootSchedule()
    {
        return $this->belongsTo(ShootSchedule::class);
    }

    public function concept()
    {
        return $this->belongsTo(Concept::class);
    }
}
