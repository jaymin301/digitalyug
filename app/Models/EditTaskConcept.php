<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EditTaskConcept extends Pivot
{
    protected $table = 'edit_task_concept';

    // public $incrementing = true; // because you have an id column

    protected $fillable = [
        'edit_task_id',
        'concept_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function editTask()
    {
        return $this->belongsTo(EditTask::class);
    }

    public function concept()
    {
        return $this->belongsTo(Concept::class);
    }
}