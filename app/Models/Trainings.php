<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainings extends Model
{
    protected $primaryKey = 'training_id';

    protected $fillable = [
        'personnel_id',
        'course_id',
        'start_date',
        'end_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'personnel_id', 'personnel_id');
    }

    public function courses()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'course_id');
    }
}
