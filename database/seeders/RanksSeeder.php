<?php

namespace Database\Seeders;

use App\Models\Ranks;
use Illuminate\Database\Seeder;

class RanksSeeder extends Seeder
{
    public function run(): void
    {

        $ranks = [
            [
                'rank_name' => 'General',
                'abbreviation' => 'GEN',
                'level' => 19,

            ],
            [
                'rank_name' => 'Lieutenant General',
                'abbreviation' => 'LTGEN',
                'level' => 19,

            ],
            [
                'rank_name' => 'Major General',
                'abbreviation' => 'MAJGEN',
                'level' => 17,

            ],
            [
                'rank_name' => 'Brigadier General',
                'abbreviation' => 'BGEN',
                'level' => 16,

            ],
            [
                'rank_name' => 'Colonel',
                'abbreviation' => 'COL',
                'level' => 15,

            ],
            [
                'rank_name' => 'Lieutenant Colonel',
                'abbreviation' => 'LTC',
                'level' => 14,

            ],
            [
                'rank_name' => 'Major',
                'abbreviation' => 'MAJ',
                'level' => 13,

            ],
            [
                'rank_name' => 'Captain',
                'abbreviation' => 'CPT',
                'level' => 12,

            ],
            [
                'rank_name' => 'First Lieutenant',
                'abbreviation' => '1LT',
                'level' => 11,

            ],
            [
                'rank_name' => 'Second Lieutenant',
                'abbreviation' => '2LT',
                'level' => 10,

            ],
            [
                'rank_name' => 'First Chief Master Sergeant',
                'abbreviation' => 'FCMS',
                'level' => 9,

            ],
            [
                'rank_name' => 'Chief Master Sergeant',
                'abbreviation' => 'CMS',
                'level' => 8,

            ],
            [
                'rank_name' => 'Master Sergeant',
                'abbreviation' => 'MSG',
                'level' => 7,

            ],
            [
                'rank_name' => 'Technical Sergeant',
                'abbreviation' => 'TSG',
                'level' => 6,

            ],
            [
                'rank_name' => 'Staff Sergeant',
                'abbreviation' => 'SSG',
                'level' => 5,

            ],
            [
                'rank_name' => 'Sergeant',
                'abbreviation' => 'SGT',
                'level' => 4,

            ],
            [
                'rank_name' => 'Corporal',
                'abbreviation' => 'CPL',
                'level' => 3,

            ],
            [
                'rank_name' => 'Private First Class',
                'abbreviation' => 'PFC',
                'level' => 2,

            ],
            [
                'rank_name' => 'Private',
                'abbreviation' => 'PVT',
                'level' => 1,

            ],

        ];
        foreach ($ranks as $rank) {
            Ranks::create($rank);
        }
    }
}
