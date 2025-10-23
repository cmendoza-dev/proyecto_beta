<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by'
    ];

    protected $table = 'documents';

    public function meeting()
    {
        return $this->belongsTo(\App\Models\Meeting::class);
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}
