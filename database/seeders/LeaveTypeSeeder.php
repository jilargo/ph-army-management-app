<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $leave_type = [
            [
                'leave_name' => 'Vacation Leave',
                'description' => 'Leave granted for rest, recreation, and personal activities.',
            ],
            [
                'leave_name' => 'Sick Leave',
                'description' => 'Leave granted due to illness or medical reasons.',
            ],
            [
                'leave_name' => 'Emergency Leave',
                'description' => 'Leave granted for urgent personal or family matters.',
            ],
        ];
        foreach ($leave_type as $leave) {
            LeaveType::create($leave);
        }

    }
}
