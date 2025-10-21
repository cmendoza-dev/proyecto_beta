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

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Primer nombre desde 'name' (si solo guardas el nombre completo en 'name')
    public function getFirstNameAttribute(): string
    {
        $name = (string) ($this->attributes['name'] ?? '');
        $parts = preg_split('/\s+/', trim($name));
        return $parts[0] ?? '';
    }

 // Usar la columna 'last_name' si existe; si no, intentar derivarlo desde 'name'
    public function getLastNameAttribute($value): string
    {
        if (!is_null($value) && $value !== '') {
            return (string) $value;
        }

        $name = (string) ($this->attributes['name'] ?? '');
        $parts = preg_split('/\s+/', trim($name));
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }
}
