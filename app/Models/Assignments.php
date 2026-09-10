<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignments extends Model
{
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'personnel_id',
        'unit_id',
        'rank_id',
        'position',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function personnel()
    {
        return $this->belongsTo(
            Personnel::class,
            'personnel_id',
            'personnel_id'
        );
    }

    public function unit()
    {
        return $this->belongsTo(
            Units::class,
            'unit_id',
            'unit_id'
        );
    }

    public function rank()
    {
        return $this->belongsTo(
            Ranks::class,
            'rank_id',
            'rank_id'
        );
    }
}
