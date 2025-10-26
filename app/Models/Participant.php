<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'dni',
        'phone',
        'email',
        'organization',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function meetings()
    {
        return $this->belongsToMany(
            \App\Models\Meeting::class,
            'attendances',
            'participant_id',
            'meeting_id'
        )->withPivot(['attended_at', 'status', 'notes', 'created_at', 'updated_at']);
    }

    // Accessor conveniente
    public function getFullNameAttribute(): string
    {
        return trim(($this->attributes['name'] ?? '') . ' ' . ($this->attributes['last_name'] ?? ''));
    }

    // Mutators de normalización
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->cleanName($value);
    }

    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = $this->cleanName($value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim((string) $value));
    }

    public function setDniAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['dni'] = substr($digits, 0, 8); // ajusta si tu DNI es 8 u 12
    }

    public function setPhoneAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['phone'] = substr($digits, 0, 9); // ajusta si tu teléfono es 9 o más dígitos
    }

    public function setOrganizationAttribute($value): void
    {
        $this->attributes['organization'] = $this->cleanText($value);
    }

    public function setPositionAttribute($value): void
    {
        $this->attributes['position'] = $this->cleanText($value);
    }

    private function cleanName(?string $value): string
    {
        $v = trim((string) $value);
        // Normalización ligera sin forzar mayúsculas por acentos
        $v = preg_replace('/\s+/', ' ', $v);
        return $v;
    }

    private function cleanText(?string $value): string
    {
        $v = trim((string) $value);
        return preg_replace('/\s+/', ' ', $v);
    }
}
