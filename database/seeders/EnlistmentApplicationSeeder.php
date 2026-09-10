<?php

namespace Database\Seeders;

use App\Models\EnlistmentApplication;
use Illuminate\Database\Seeder;

class EnlistmentApplicationSeeder extends Seeder
{
    /**
     * Seed a handful of pending enlistment applications for demo purposes.
     */
    public function run(): void
    {
        $applicants = [
            [
                'first_name' => 'Carl',
                'middle_name' => 'Domingo',
                'last_name' => 'Santos',
                'date_of_birth' => '2001-04-18',
                'gender' => 'Male',
                'contact_number' => '09172345678',
                'personal_email' => 'carl.santos@apply.test',
                'address' => 'San Fernando, Pampanga',
            ],
            [
                'first_name' => 'Katrina',
                'middle_name' => 'Reyes',
                'last_name' => 'Aquino',
                'date_of_birth' => '2000-08-02',
                'gender' => 'Female',
                'contact_number' => '09179876543',
                'personal_email' => 'katrina.aquino@apply.test',
                'address' => 'Baguio City, Benguet',
            ],
            [
                'first_name' => 'Miguel',
                'middle_name' => 'Torres',
                'last_name' => 'Bautista',
                'date_of_birth' => '1999-11-27',
                'gender' => 'Male',
                'contact_number' => '09171234567',
                'personal_email' => 'miguel.bautista@apply.test',
                'address' => 'Davao City, Davao del Sur',
            ],
        ];

        foreach ($applicants as $applicant) {
            EnlistmentApplication::create($applicant + ['status' => 'pending']);
        }
    }
}
