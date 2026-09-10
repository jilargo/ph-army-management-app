<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
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

        $personnels = Personnel::inRandomOrder()->take(3)->get();
        $personnelIds = $personnels->pluck('personnel_id')->all();

        $now = Carbon::now();
        $firstOfMonth = $now->copy()->startOfMonth();
        $lastOfMonth = $now->copy()->endOfMonth();

        $sampleTasks = [
            ['title' => 'Morning physical fitness training', 'type' => 'training', 'priority' => 'medium', 'status' => 'completed'],
            ['title' => 'Unit logistics inventory', 'type' => 'logistics', 'priority' => 'high', 'status' => 'in_progress'],
            ['title' => 'Weapons maintenance inspection', 'type' => 'operations', 'priority' => 'high', 'status' => 'pending'],
            ['title' => 'Quarterly personnel records audit', 'type' => 'administrative', 'priority' => 'medium', 'status' => 'in_progress'],
            ['title' => 'Field exercise preparation', 'type' => 'operations', 'priority' => 'high', 'status' => 'pending'],
            ['title' => 'New recruit orientation briefing', 'type' => 'training', 'priority' => 'low', 'status' => 'pending'],
            ['title' => 'Barracks inspection', 'type' => 'administrative', 'priority' => 'medium', 'status' => 'completed'],
            ['title' => 'Communication equipment check', 'type' => 'logistics', 'priority' => 'medium', 'status' => 'pending'],
            ['title' => 'Security perimeter patrol scheduling', 'type' => 'operations', 'priority' => 'high', 'status' => 'in_progress'],
            ['title' => 'Monthly readiness report submission', 'type' => 'administrative', 'priority' => 'high', 'status' => 'pending'],
        ];

        $dueDates = [];
        $count = count($sampleTasks);
        $step = max(1, intdiv($firstOfMonth->diffInDays($lastOfMonth), $count));

        foreach ($sampleTasks as $index => $task) {
            $dueDate = $firstOfMonth->copy()->addDays($index * $step)->addDays(rand(0, 2));
            if ($dueDate->gt($lastOfMonth)) {
                $dueDate = $lastOfMonth->copy()->subDays(rand(0, 3));
            }

            $task['user_id'] = $admin->id;
            $task['personnel_id'] = $personnelIds ? ($personnelIds[$index % count($personnelIds)] ?? null) : null;
            $task['due_date'] = $dueDate;
            $task['description'] = $task['title'].' scheduled for '.$dueDate->format('F j, Y');
            $task['start_time'] = '08:00:00';
            $task['end_time'] = '17:00:00';

            Task::create($task);
        }
    }
}
