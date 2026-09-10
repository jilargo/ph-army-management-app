<?php

use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@edit.test']);

    $this->rank = Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $formation = ParentUnit::create(['parent_name' => 'Armor Division']);

    $this->firstUnit = Units::create([
        'parent_id' => $formation->parent_id,
        'unit_name' => '1st Cavalry Squadron',
        'unit_code' => '1CAVSQ',
        'location' => 'Camp',
        'status' => 'Active',
    ]);

    $this->secondUnit = Units::create([
        'parent_id' => $formation->parent_id,
        'unit_name' => '2nd Cavalry Squadron',
        'unit_code' => '2CAVSQ',
        'location' => 'Camp',
        'status' => 'Active',
    ]);

    $this->personnel = Personnel::create([
        'rank_id' => $this->rank->rank_id,
        'unit_id' => $this->firstUnit->unit_id,
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'date_of_birth' => '1995-01-01',
        'gender' => 'Male',
        'status' => 'Active',
        'date_of_entry' => '2015-06-01',
        'personal_email' => 'juan@example.com',
        'contact_number' => '09170000000',
        'address' => 'Manila',
    ]);
});

it('shows the unit combo box on the personnel edit page', function () {
    $this->actingAs($this->admin)
        ->get(route('personnel.edit', $this->personnel->personnel_id))
        ->assertOk()
        ->assertSee('Select unit')
        ->assertSee('1st Cavalry Squadron')
        ->assertSee('2nd Cavalry Squadron');
});

it('updates the unit of a soldier when saving', function () {
    Livewire::actingAs($this->admin)
        ->test('personnel.edit', ['personnel' => $this->personnel])
        ->set('unit_id', (string) $this->secondUnit->unit_id)
        ->call('update_personnel')
        ->assertRedirect(route('personnel.index'));

    expect($this->personnel->fresh()->unit_id)->toBe($this->secondUnit->unit_id);
});
