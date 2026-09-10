<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $primaryKey = 'task_id';

    protected $fillable = [
        'user_id',
        'personnel_id',
        'title',
        'description',
        'due_date',
        'start_time',
        'end_time',
        'priority',
        'status',
        'type',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(Personnel::class, 'task_personnel', 'task_id', 'personnel_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }
}
