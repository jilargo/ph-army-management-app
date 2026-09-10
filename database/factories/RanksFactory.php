<?php

namespace Database\Factories;

use App\Models\ranks;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ranks>
 */
class RanksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ranks = [
            ['rank_name' => 'General', 'abbreviation' => 'GEN'],
            ['rank_name' => 'Lieutenant General', 'abbreviation' => 'LTGEN'],
            ['rank_name' => 'Major General', 'abbreviation' => 'MAJGEN'],
            ['rank_name' => 'Brigadier General', 'abbreviation' => 'BGEN'],
            ['rank_name' => 'Colonel', 'abbreviation' => 'COL'],
            ['rank_name' => 'Lieutenant Colonel', 'abbreviation' => 'LTC'],
            ['rank_name' => 'Major', 'abbreviation' => 'MAJ'],
            ['rank_name' => 'Captain', 'abbreviation' => 'CPT'],
            ['rank_name' => 'First Lieutenant', 'abbreviation' => '1LT'],
            ['rank_name' => 'Second Lieutenant', 'abbreviation' => '2LT'],
            ['rank_name' => 'First Chief Master Sergeant', 'abbreviation' => 'FCMS'],
            ['rank_name' => 'Chief Master Sergeant', 'abbreviation' => 'CMS'],
            ['rank_name' => 'Master Sergeant', 'abbreviation' => 'MSG'],
            ['rank_name' => 'Technical Sergeant', 'abbreviation' => 'TSG'],
            ['rank_name' => 'Staff Sergeant', 'abbreviation' => 'SSG'],
            ['rank_name' => 'Sergeant', 'abbreviation' => 'SGT'],
            ['rank_name' => 'Corporal', 'abbreviation' => 'CPL'],
            ['rank_name' => 'Private First Class', 'abbreviation' => 'PFC'],
            ['rank_name' => 'Private', 'abbreviation' => 'PVT'],
        ];

        $rank = fake()->randomElement($ranks);

        return [
            'rank_name' => $rank['rank_name'],
            'abbreviation' => $rank['abbreviation'],
            'level' => fake()->randomElement([1, 2, 3, 4, 5]),
        ];
    }
}
