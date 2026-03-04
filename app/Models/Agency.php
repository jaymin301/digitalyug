<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'contact',
        'remark'
    ];

    /**
     * Get the leads associated with the agency.
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
