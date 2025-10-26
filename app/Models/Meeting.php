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
        'location',
        'type_meeting',
        'date',
        'opening_time', // <- agregar
        'closing_time', // <- agregar
        'status',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_time' => 'datetime:H:i', // <- agregar
        'closing_time' => 'datetime:H:i',  // <- agregar
        'attachments' => 'array',  // ← Asegúrate de tener esto
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
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
        if (! empty($this->attributes['opening_time'])) {
            return (string) $this->attributes['opening_time'];
        }

        return isset($this->attributes['time']) ? (string) $this->attributes['time'] : null;
    }

    public function getClosingTimeAttribute(): ?string
    {
        if (! empty($this->attributes['closing_time'])) {
            return (string) $this->attributes['closing_time'];
        }

        return null; // si no manejas hora de cierre aún
    }

    public function participants()
    {
        return $this->belongsToMany(
            \App\Models\Participant::class,
            'attendances',
            'meeting_id',
            'participant_id'
        )->withPivot(['attended_at', 'status', 'notes', 'created_at', 'updated_at']);
    }

    // Opcional: incluir en arrays/JSON automáticamente
    protected $appends = ['status_label', 'status_badge_classes'];

    // Estado en español
    public function getStatusLabelAttribute(): string
    {
        switch ($this->status) {
            case 'draft':
                return 'Borrador';
            case 'open':
                return 'Abierto';
            case 'closed':
                return 'Cerrado';
            default:
                return (string) $this->status;
        }
    }

    // Clases CSS para el badge según estado (para limpiar el Blade)
    public function getStatusBadgeClassesAttribute(): string
    {
        switch ($this->status) {
            case 'open':
                return 'bg-green-100 text-green-800';
            case 'closed':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-yellow-100 text-yellow-800';
        }
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\Document::class);
    }

}
