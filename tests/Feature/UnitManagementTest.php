<?php

use App\Models\ParentUnit;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@units.test']);
    $this->soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@units.test']);
    $this->formation = ParentUnit::create(['parent_name' => '1st Infantry (Tabak) Division']);
});

it('lets an admin open the unit management page', function () {
    $this->actingAs($this->admin)
        ->get('/units')
        ->assertStatus(200)
        ->assertSee('Add Unit', escape: false);
});

it('lets an admin add a unit under an existing formation', function () {
    Livewire::actingAs($this->admin)
        ->test('units.index')
        ->set('parent_id', (string) $this->formation->parent_id)
        ->set('unit_name', '1st Cavalry Squadron')
        ->set('unit_code', '1CAVSQ')
        ->set('location', "Camp O'Donnell, Capas, Tarlac")
        ->set('status', 'Active')
        ->call('storeUnit')
        ->assertHasNoErrors()
        ->assertSet('unit_name', '');

    $this->assertDatabaseHas('units', [
        'unit_name' => '1st Cavalry Squadron',
        'unit_code' => '1CAVSQ',
        'parent_id' => $this->formation->parent_id,
    ]);
});

it('lets an admin add a brand new formation plus a unit in one go', function () {
    Livewire::actingAs($this->admin)
        ->test('units.index')
        ->set('new_parent_name', '5th Infantry (Star) Division')
        ->set('unit_name', '5th Infantry Battalion')
        ->set('unit_code', '5IB')
        ->set('location', 'Isabela')
        ->set('status', 'Active')
        ->call('storeUnit')
        ->assertHasNoErrors();

    $parent = ParentUnit::where('parent_name', '5th Infantry (Star) Division')->first();
    expect($parent)->not->toBeNull();

    $this->assertDatabaseHas('units', [
        'unit_name' => '5th Infantry Battalion',
        'unit_code' => '5IB',
        'parent_id' => $parent->parent_id,
    ]);
});

it('rejects a duplicate unit code', function () {
    Units::create([
        'parent_id' => $this->formation->parent_id,
        'unit_name' => '1CAVSQ Existing',
        'unit_code' => '1CAVSQ',
        'location' => 'Camp',
        'status' => 'Active',
    ]);

    Livewire::actingAs($this->admin)
        ->test('units.index')
        ->set('parent_id', (string) $this->formation->parent_id)
        ->set('unit_name', '1st Cavalry Squadron')
        ->set('unit_code', '1CAVSQ')
        ->set('location', 'Camp')
        ->set('status', 'Active')
        ->call('storeUnit')
        ->assertHasErrors(['unit_code']);
});

it('requires either an existing formation or a new formation name', function () {
    Livewire::actingAs($this->admin)
        ->test('units.index')
        ->set('unit_name', 'Ghost Unit')
        ->set('unit_code', 'GHOST')
        ->set('location', 'Nowhere')
        ->set('status', 'Active')
        ->call('storeUnit')
        ->assertHasErrors(['parent_id']);
});

it('does not let a non-admin access the unit management page', function () {
    $this->actingAs($this->soldier)
        ->get('/units')
        ->assertForbidden();
});

it('does not let a non-admin add a unit', function () {
    Livewire::actingAs($this->soldier)
        ->test('units.index')
        ->set('parent_id', (string) $this->formation->parent_id)
        ->set('unit_name', 'Sneaky Unit')
        ->set('unit_code', 'SNEAKY')
        ->set('location', 'Anywhere')
        ->set('status', 'Active')
        ->call('storeUnit')
        ->assertForbidden();
});
