<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ranks extends Model
{
    protected $primaryKey = 'rank_id';

    protected $table = 'ranks';

    protected $fillable = [
        'rank_name',
        'abbreviation',
        'level',
    ];

    public function personnel()
    {
        return $this->hasMany(personnel::class, 'rank_id', 'rank_id');
    }

    public function promotionFromRank()
    {
        return $this->hasMany(Promotions::class, 'from_rank_id', 'rank_id');
    }

    public function promotionToRank()
    {
        return $this->hasMany(Promotions::class, 'to_rank_id', 'rank_id');
    }
}
