<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'participant_id',
        'attended_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    protected $table = 'attendances';

    public function meeting()
    {
        return $this->belongsTo(\App\Models\Meeting::class);
    }

    public function participant()
    {
        return $this->belongsTo(\App\Models\Participant::class);
    }

    public function getRegisteredAtAttribute(): ?Carbon
    {
        return $this->attended_at ? Carbon::parse($this->attended_at) : null;
    }
}
