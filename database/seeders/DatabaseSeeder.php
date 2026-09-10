<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'James Ian Largo',
            'email' => 'eyan.laradev@gmail.com',
            'password' => '12345678',
            'role' => 'admin',
        ]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => '12345678',
            'role' => 'admin',
        ]);
        $this->call([
            ParentUnitSeeder::class,
            RanksSeeder::class,
            UnitsSeeder::class,
            LeaveTypeSeeder::class,
            PersonnelSeeder::class,
            TaskSeeder::class,
            LeaveSeeder::class,
            PromotionSeeder::class,
            EnlistmentApplicationSeeder::class,
        ]);
    }
}
