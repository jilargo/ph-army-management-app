<?php

namespace App\Models;

use Database\Factories\UnitsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Units extends Model
{
    protected $primaryKey = 'unit_id';

    protected $fillable = [
        'parent_id',
        'unit_name',
        'unit_code',
        'location',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentUnit::class, 'parent_id', 'parent_id');
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class, 'unit_id', 'unit_id');
    }

    /** @use HasFactory<UnitsFactory> */
    use HasFactory;
}
