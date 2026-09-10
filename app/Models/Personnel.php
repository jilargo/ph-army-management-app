<?php

namespace App\Models;

use Database\Factories\PersonnelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Personnel extends Model
{
    protected $primaryKey = 'personnel_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'suffix',
        'status',
        'rank_id',
        'unit_id',
        'date_of_entry',
        'personal_email',
        'contact_number',
        'address',
        'profile_picture',
    ];

    public function user()
    {
        // This Personnel belongs to a User. The Personnel's user_id points to the User's id
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function rank()
    {
        return $this->belongsTo(Ranks::class, 'rank_id', 'rank_id');
    }

    public function units()
    {
        return $this->belongsTo(Units::class, 'unit_id', 'unit_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'personnel_id', 'personnel_id');
    }

    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_personnel', 'personnel_id', 'task_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function leaves()
    {
        return $this->hasMany(Leaves::class, 'personnel_id', 'personnel_id');
    }

    public function promotions()
    {
        return $this->hasMany(Promotions::class, 'personnel_id', 'personnel_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignments::class, 'personnel_id', 'personnel_id');
    }

    public function trainings()
    {
        return $this->hasMany(Trainings::class, 'personnel_id', 'personnel_id');
    }

    /** @use HasFactory<PersonnelFactory> */
    use HasFactory;

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->profile_picture
            ? Storage::disk('public')->url($this->profile_picture)
            : null;
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }
}
