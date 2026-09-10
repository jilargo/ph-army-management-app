<?php

namespace Database\Seeders;

use App\Models\ParentUnit;
use App\Models\Units;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    /**
     * Seed battalions, brigades, regiments, and squadrons under each
     * major Philippine Army formation.
     */
    public function run(): void
    {
        $formations = ParentUnit::orderBy('parent_id')->get();

        $units = [
            '1st Infantry (Tabak) Division' => [
                ['1st Infantry Battalion', '1IB', 'Ilocos Sur Area'],
                ['2nd Infantry Battalion', '2IB', 'Cagayan Valley Area'],
                ['501st Infantry Brigade', '501IBde', 'Lingayen, Pangasinan'],
                ['502nd Infantry Brigade', '502IBde', 'Ilagan, Isabela'],
            ],
            '7th Infantry (Kaugnay) Division' => [
                ['7th Infantry Battalion', '7IB', 'Fort Ramon Magsaysay, Nueva Ecija'],
                ['56th Infantry Battalion', '56IB', 'Aurora Province'],
                ['703rd Infantry Brigade', '703IBde', 'Fort Ramon Magsaysay, Nueva Ecija'],
            ],
            '9th Infantry (Spear) Division' => [
                ['9th Infantry Battalion', '9IB', 'Camarines Sur'],
                ['901st Infantry Brigade', '901IBde', 'Legazpi City, Albay'],
                ['902nd Infantry Brigade', '902IBde', 'Pili, Camarines Sur'],
            ],
            'Armor Division' => [
                ['1st Cavalry Squadron', '1CAVSQ', 'Camp O\'Donnell, Capas, Tarlac'],
                ['2nd Cavalry Squadron', '2CAVSQ', 'Camp O\'Donnell, Capas, Tarlac'],
                ['1st Mechanized Infantry Battalion', '1MIB', 'Camp O\'Donnell, Capas, Tarlac'],
            ],
            'Special Operations Command' => [
                ['1st Scout Ranger Regiment', '1SRR', 'Fort Ramon Magsaysay, Nueva Ecija'],
                ['Special Forces Regiment', 'SFR', 'Fort Ramon Magsaysay, Nueva Ecija'],
                ['Light Reaction Regiment', 'LRR', 'Fort Ramon Magsaysay, Nueva Ecija'],
            ],
            'Army Support Command' => [
                ['Army Signal Regiment', 'ASR', 'Fort Bonifacio, Taguig City'],
                ['Army Civil-Military Operations Regiment', 'ACMOR', 'Fort Bonifacio, Taguig City'],
            ],
        ];

        foreach ($formations as $formation) {
            foreach ($units[$formation->parent_name] ?? [] as [$unitName, $unitCode, $location]) {
                Units::create([
                    'parent_id' => $formation->parent_id,
                    'unit_name' => $unitName,
                    'unit_code' => $unitCode,
                    'location' => $location,
                    'status' => 'Active',
                ]);
            }
        }
    }
}
