<?php

namespace Database\Seeders;

use App\Models\Leaves;
use App\Models\Personnel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => '12345678',
            'role' => 'admin',
        ]);

        $personnels = Personnel::inRandomOrder()->take(4)->get();

        $now = Carbon::now();

        $sampleLeaves = [
            [
                'leave_type_id' => 1,
                'start_date' => $now->copy()->addDays(3)->toDateString(),
                'end_date' => $now->copy()->addDays(6)->toDateString(),
                'reason' => 'Family vacation and rest.',
                'status' => 'pending',
                'personnel_index' => 0,
            ],
            [
                'leave_type_id' => 2,
                'start_date' => $now->copy()->subDays(3)->toDateString(),
                'end_date' => $now->copy()->subDay()->toDateString(),
                'reason' => 'Medical checkup following a training injury.',
                'status' => 'approved',
                'remarks' => 'Approved. Rest and recover well, soldier.',
                'approved_at' => $now->copy()->subDays(7),
                'personnel_index' => 1,
            ],
            [
                'leave_type_id' => 3,
                'start_date' => $now->copy()->addDays(1)->toDateString(),
                'end_date' => $now->copy()->addDays(2)->toDateString(),
                'reason' => 'Urgent family matter requiring immediate attention.',
                'status' => 'pending',
                'personnel_index' => 2,
            ],
            [
                'leave_type_id' => 2,
                'start_date' => $now->copy()->addDays(10)->toDateString(),
                'end_date' => $now->copy()->addDays(12)->toDateString(),
                'reason' => 'Scheduled surgery and recovery.',
                'status' => 'rejected',
                'remarks' => 'Rejected - operation schedule conflict. Please reschedule.',
                'personnel_index' => 3,
            ],
            [
                'leave_type_id' => 1,
                'start_date' => $now->copy()->subDays(12)->toDateString(),
                'end_date' => $now->copy()->subDays(9)->toDateString(),
                'reason' => 'Personal rest and recreation.',
                'status' => 'approved',
                'remarks' => 'Approved.',
                'approved_at' => $now->copy()->subDays(15),
                'personnel_index' => 0,
            ],
        ];

        foreach ($sampleLeaves as $index => $leave) {
            $personnel = $personnels[$leave['personnel_index']] ?? $personnels->first();
            if (! $personnel) {
                continue;
            }

            $data = [
                'personnel_id' => $personnel->personnel_id,
                'user_id' => $personnel->user_id ?? $admin->id,
                'leave_type_id' => $leave['leave_type_id'],
                'start_date' => $leave['start_date'],
                'end_date' => $leave['end_date'],
                'reason' => $leave['reason'],
                'status' => $leave['status'],
                'remarks' => $leave['remarks'] ?? null,
                'approved_by' => $leave['status'] === 'approved' ? $admin->id : null,
                'approved_at' => $leave['approved_at'] ?? null,
            ];

            Leaves::create($data);
        }
    }
}
