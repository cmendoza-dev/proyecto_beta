<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization',
        'position',
        'dni',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFirstNameAttribute(): string
    {
        $name = (string) ($this->attributes['name'] ?? '');
        return trim(explode(' ', $name, 2)[0] ?? '');
    }

    public function getLastNameAttribute(): string
    {
        $name = (string) ($this->attributes['name'] ?? '');
        $parts = explode(' ', $name, 2);
        return isset($parts[1]) ? trim($parts[1]) : '';
    }
}
