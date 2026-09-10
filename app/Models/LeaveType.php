<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $primaryKey = 'leave_type_id';

    protected $fillable = [
        'leave_name',
        'description',
    ];

    public function leaves()
    {
        return $this->hasMany(leaves::class, 'leave_type_id', 'leave_type_id');
    }
}
