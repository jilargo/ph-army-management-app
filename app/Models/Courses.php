<?php

namespace App\Models;

use Database\Factories\CoursesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $primaryKey = 'course_id';

    protected $fillable = [
        'course_id',
        'course_name',
        'course_abbreviation',
    ];

    /** @use HasFactory<CoursesFactory> */
    use HasFactory;
}
