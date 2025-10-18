<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'opening_time', // <- agregar
        'closing_time', // <- agregar
        'location',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_time' => 'datetime:H:i', // <- agregar
        'closing_time' => 'datetime:H:i',  // <- agregar
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    // Compatibilidad con la vista: si no existen columnas opening_time/closing_time,
    // usa el campo 'time' (si lo tienes) o null.
    public function getOpeningTimeAttribute(): ?string
    {
        // ajusta si tienes columnas reales
        if (!empty($this->attributes['opening_time'])) {
            return (string) $this->attributes['opening_time'];
        }
        return isset($this->attributes['time']) ? (string) $this->attributes['time'] : null;
    }

    public function getClosingTimeAttribute(): ?string
    {
        if (!empty($this->attributes['closing_time'])) {
            return (string) $this->attributes['closing_time'];
        }
        return null; // si no manejas hora de cierre aún
    }
}
