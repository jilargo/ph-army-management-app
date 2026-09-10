<?php

namespace Database\Seeders;

use App\Models\ParentUnit;
use Illuminate\Database\Seeder;

class ParentUnitSeeder extends Seeder
{
    /**
     * Seed the Philippine Army's major formations (divisions and commands).
     */
    public function run(): void
    {
        $formations = [
            '1st Infantry (Tabak) Division',
            '7th Infantry (Kaugnay) Division',
            '9th Infantry (Spear) Division',
            'Armor Division',
            'Special Operations Command',
            'Army Support Command',
            'Army Reserve Command',
        ];

        foreach ($formations as $parentName) {
            ParentUnit::create(['parent_name' => $parentName]);
        }
    }
}
