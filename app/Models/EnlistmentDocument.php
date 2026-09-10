<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EnlistmentDocument extends Model
{
    protected $fillable = [
        'application_id',
        'file_path',
        'original_name',
        'mime_type',
    ];

    public function application()
    {
        return $this->belongsTo(EnlistmentApplication::class, 'application_id', 'id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}
