<?php

namespace Database\Seeders;

use App\Models\Courses;
use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Courses::create([
            'course_name' => 'Basic Citizen Military Training',
            'course_abbreviation' => 'BCMT',
        ]);
        Courses::create([
            'course_name' => 'Candidate Soldier Course',
            'course_abbreviation' => 'CSC',
        ]);
        Courses::create([
            'course_name' => 'Scout Ranger Course',
            'course_abbreviation' => 'SRC',
        ]);
        Courses::create([
            'course_name' => 'Special Forces Operations Course',
            'course_abbreviation' => 'SFOC',
        ]);
        Courses::create([
            'course_name' => 'Light Armor Course',
            'course_abbreviation' => 'LAC',
        ]);
        Courses::create([
            'course_name' => 'Civil Military Operations Course',
            'course_abbreviation' => 'CMOC',
        ]);
        Courses::create([
            'course_name' => 'Medical Service Course',
            'course_abbreviation' => 'MSC',
        ]);
        Courses::create([
            'course_name' => 'Officer Candidate Course',
            'course_abbreviation' => 'OCS',
        ]);
    }
}
