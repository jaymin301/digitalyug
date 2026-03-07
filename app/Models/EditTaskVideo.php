<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditTaskVideo extends Model
{
    protected $fillable = [
        'edit_task_id', 'concept_id', 'video_label', 'status', 'notes'
    ];

    public function editTask()
    {
        return $this->belongsTo(EditTask::class);
    }

    public function concept()
    {
        return $this->belongsTo(Concept::class);
    }
}