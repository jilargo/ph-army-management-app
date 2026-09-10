<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotions extends Model
{
    protected $primaryKey = 'promotion_id';

    protected $fillable = [
        'personnel_id',
        'from_rank_id',
        'to_rank_id',
        'promotion_date',
        'status',
        'remarks',
        'recommendation',
        'approved_at',
        'recommended_by',
        'approved_by',
    ];

    protected $casts = [
        'promotion_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }

    public function fromRank()
    {
        return $this->belongsTo(Ranks::class, 'from_rank_id', 'rank_id');
    }

    public function toRank()
    {
        return $this->belongsTo(Ranks::class, 'to_rank_id', 'rank_id');
    }

    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommended_by', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
