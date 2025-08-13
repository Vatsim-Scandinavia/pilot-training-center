<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilotTrainingInterest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'deadline' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function pilotTraining()
    {
        return $this->belongsTo(PilotTraining::class);
    }
}
