<?php

use App\Models\EnlistmentApplication;
use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Task;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@report.test', 'name' => 'Admin Officer']);
    $this->soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@report.test']);

    Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $formation = ParentUnit::create(['parent_name' => '1st Infantry (Tabak) Division']);

    Units::create([
        'parent_id' => $formation->parent_id,
        'unit_name' => '1st Infantry Battalion',
        'unit_code' => '1IB',
        'location' => 'Camp',
        'status' => 'Active',
    ]);

    Personnel::create([
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'date_of_entry' => '2018-06-01',
        'status' => 'Active',
    ]);
});

it('opens the reports page', function () {
    $this->actingAs($this->admin)
        ->get('/reports')
        ->assertStatus(200)
        ->assertSee('Download PDF Report', escape: false);
});

it('lets an admin download the personnel report as a real PDF', function () {
    $response = $this->actingAs($this->admin)
        ->get('/reports/personnel')
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');

    $content = $response->streamedContent();
    expect(substr($content, 0, 4))->toBe('%PDF')
        ->and(strlen($content))->toBeGreaterThan(1000);
});

it('includes newly accepted soldiers and tasks in the report', function () {
    Task::create([
        'title' => 'Field Exercise Completed',
        'description' => 'Completed field exercise',
        'due_date' => now()->addDays(5),
        'priority' => 'high',
        'status' => 'completed',
        'type' => 'operations',
        'user_id' => $this->admin->id,
        'personnel_id' => 1,
    ]);

    Personnel::create([
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'New',
        'last_name' => 'Recruit',
        'date_of_entry' => now()->subDays(5)->toDateString(),
        'status' => 'Active',
    ]);

    EnlistmentApplication::factory()->create([
        'first_name' => 'Pending',
        'last_name' => 'Applicant',
        'status' => 'pending',
    ]);

    $content = $this->actingAs($this->admin)
        ->get('/reports/personnel')
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf')
        ->streamedContent();

    expect(substr($content, 0, 4))->toBe('%PDF');
});

it('does not let a soldier access the reports page or download', function () {
    $this->actingAs($this->soldier)
        ->get('/reports')
        ->assertForbidden();

    $this->actingAs($this->soldier)
        ->get('/reports/personnel')
        ->assertForbidden();
});
