<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rankId = Ranks::orderBy('level')->value('rank_id') ?? 1;
        $unitId = Units::value('unit_id') ?? 1;

        $soldiers = [
            [
                'user' => ['name' => 'James Largo', 'email' => 'james@army.test'],
                'personnel' => [
                    'first_name' => 'James',
                    'middle_name' => 'Astillero',
                    'last_name' => 'Largo',
                    'date_of_birth' => '1996-04-12',
                    'gender' => 'Male',
                    'personal_email' => 'james.largo@gmail.com',
                    'contact_number' => '09171111111',
                    'address' => 'Quezon City, Philippines',
                    'date_of_entry' => '2018-07-01',
                ],
            ],
            [
                'user' => ['name' => 'Boyet Magneto Junior', 'email' => 'boyet@gmail.com'],
                'personnel' => [
                    'first_name' => 'Boyet',
                    'middle_name' => 'Magneto',
                    'last_name' => 'Junior',
                    'date_of_birth' => '1992-11-23',
                    'gender' => 'Male',
                    'personal_email' => 'boyet.magneto@gmail.com',
                    'contact_number' => '09172222222',
                    'address' => 'Pasig City, Philippines',
                    'date_of_entry' => '2015-03-16',
                ],
            ],
        ];

        foreach ($soldiers as $soldier) {
            $user = User::create([
                'name' => $soldier['user']['name'],
                'email' => $soldier['user']['email'],
                'password' => '12345678',
                'role' => 'user',
            ]);

            Personnel::create(array_merge($soldier['personnel'], [
                'user_id' => $user->id,
                'rank_id' => $rankId,
                'unit_id' => $unitId,
                'status' => 'Active',
            ]));
        }
    }
}
