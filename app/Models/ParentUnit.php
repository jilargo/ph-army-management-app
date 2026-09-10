<?php

namespace App\Models;

use Database\Factories\ParentUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentUnit extends Model
{
    protected $primaryKey = 'parent_id';

    protected $fillable = [
        'parent_id',
        'parent_name',
    ];

    public function units()
    {
        return $this->hasMany(units::class, 'parent_id', 'parent_id');
    }

    /** @use HasFactory<ParentUnitFactory> */
    use HasFactory;
}
