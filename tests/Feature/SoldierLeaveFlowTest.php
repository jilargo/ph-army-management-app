<?php

use App\Models\Leaves;
use App\Models\LeaveType;
use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);
    Ranks::create(['rank_name' => 'Corporal', 'abbreviation' => 'CPL', 'level' => 3]);

    $parent = ParentUnit::create(['parent_name' => '1st Infantry Division']);
    Units::create(['parent_id' => $parent->parent_id, 'unit_name' => 'Alpha Co', 'unit_code' => 'A-CO', 'location' => 'Camp', 'status' => 'Active']);

    LeaveType::create(['leave_name' => 'Vacation Leave', 'description' => 'Rest and recreation.']);
    LeaveType::create(['leave_name' => 'Sick Leave', 'description' => 'Medical reasons.']);

    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);
});

it('lets a soldier access their dashboard with file leave button', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    $this->actingAs($soldier)
        ->get(route('user-dashboard'))
        ->assertOk()
        ->assertSee('File Leave')
        ->assertSee('My Leave Requests');
});

it('prevents a soldier from accessing the admin leave review', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);

    $this->actingAs($soldier)
        ->get(route('leaves.index'))
        ->assertForbidden();
});

it('lets an admin access the leave review', function () {
    $this->actingAs($this->admin)
        ->get(route('leaves.index'))
        ->assertOk();
});

it('prevents a soldier from filing leave for another personnel', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $ownPersonnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);
    $otherPersonnel = Personnel::create([
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'status' => 'Active',
    ]);

    $this->actingAs($soldier)
        ->get(route('leaves.create'))
        ->assertOk();

    // A soldier filing for another personnel should be denied
    Livewire::actingAs($soldier)
        ->test('leaves.create')
        ->set('personnel_id', $otherPersonnel->personnel_id)
        ->set('leave_type_id', 1)
        ->set('start_date', now()->addDay()->toDateString())
        ->set('end_date', now()->addDays(2)->toDateString())
        ->set('reason', 'Personal reasons')
        ->call('store_leave')
        ->assertForbidden();
});

it('auto-fills the soldiers own personnel in the leave form', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $ownPersonnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    Livewire::actingAs($soldier)
        ->test('leaves.create')
        ->assertSet('personnel_id', $ownPersonnel->personnel_id);
});