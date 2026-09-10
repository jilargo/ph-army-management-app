<?php

use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $parent = ParentUnit::create(['parent_name' => '1st Infantry Division']);
    Units::create(['parent_id' => $parent->parent_id, 'unit_name' => 'Alpha Co', 'unit_code' => 'A-CO', 'location' => 'Camp', 'status' => 'Active']);

    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);
});

it('lets an admin create personnel with a profile picture', function () {
    $picture = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    Livewire::actingAs($this->admin)
        ->test('personnel.create')
        ->set('first_name', 'Juan')
        ->set('middle_name', 'Santos')
        ->set('last_name', 'Dela Cruz')
        ->set('suffix', 'Jr.')
        ->set('date_of_birth', '1995-01-01')
        ->set('gender', 'Male')
        ->set('status', 'Active')
        ->set('rank_id', 1)
        ->set('unit_id', 1)
        ->set('date_of_entry', '2015-06-01')
        ->set('personal_email', 'juan@example.com')
        ->set('contact_number', '09170000000')
        ->set('address', 'Manila')
        ->set('profile_picture', $picture)
        ->call('save_personnel')
        ->assertRedirect('/personnel/index');

    $personnel = Personnel::first();

    expect($personnel->profile_picture)->not->toBeNull();
    expect($personnel->avatar_url)->not->toBeNull();

    Storage::disk('public')->assertExists($personnel->profile_picture);
});

it('creates personnel without a profile picture', function () {
    Livewire::actingAs($this->admin)
        ->test('personnel.create')
        ->set('first_name', 'Juan')
        ->set('middle_name', 'Santos')
        ->set('last_name', 'Dela Cruz')
        ->set('suffix', 'Jr.')
        ->set('date_of_birth', '1995-01-01')
        ->set('gender', 'Male')
        ->set('status', 'Active')
        ->set('rank_id', 1)
        ->set('unit_id', 1)
        ->set('date_of_entry', '2015-06-01')
        ->set('personal_email', 'juan@example.com')
        ->set('contact_number', '09170000000')
        ->set('address', 'Manila')
        ->call('save_personnel')
        ->assertRedirect('/personnel/index');

    expect(Personnel::first()->profile_picture)->toBeNull();
    expect(Personnel::first()->avatar_url)->toBeNull();
});

it('rejects a non-image profile picture', function () {
    $file = UploadedFile::fake()->create('avatar.txt', 100);

    Livewire::actingAs($this->admin)
        ->test('personnel.create')
        ->set('first_name', 'Juan')
        ->set('middle_name', 'Santos')
        ->set('last_name', 'Dela Cruz')
        ->set('suffix', 'Jr.')
        ->set('date_of_birth', '1995-01-01')
        ->set('gender', 'Male')
        ->set('status', 'Active')
        ->set('rank_id', 1)
        ->set('unit_id', 1)
        ->set('date_of_entry', '2015-06-01')
        ->set('personal_email', 'juan@example.com')
        ->set('contact_number', '09170000000')
        ->set('address', 'Manila')
        ->set('profile_picture', $file)
        ->call('save_personnel')
        ->assertHasErrors(['profile_picture']);

    expect(Personnel::count())->toBe(0);
});

it('replaces the profile picture when editing personnel', function () {
    $personnel = Personnel::create([
        'rank_id' => 1,
        'unit_id' => 1,
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
        'profile_picture' => 'personnel/old-avatar.jpg',
    ]);

    Storage::disk('public')->put('personnel/old-avatar.jpg', 'contents');

    $newPicture = UploadedFile::fake()->image('new-avatar.jpg', 200, 200);

    Livewire::actingAs($this->admin)
        ->test('personnel.edit', ['personnel' => $personnel])
        ->set('suffix', 'Jr.')
        ->set('profile_picture', $newPicture)
        ->call('update_personnel')
        ->assertRedirect(route('personnel.index'));

    $personnel->refresh();

    expect($personnel->profile_picture)->not->toBe('personnel/old-avatar.jpg');

    Storage::disk('public')->assertExists($personnel->profile_picture);
    Storage::disk('public')->assertMissing('personnel/old-avatar.jpg');
});

it('renders the profile picture on the personnel pages', function () {
    $personnel = Personnel::create([
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
        'profile_picture' => 'personnel/avatar.jpg',
    ]);

    Storage::disk('public')->put('personnel/avatar.jpg', 'contents');

    $this->actingAs($this->admin)
        ->get('/personnel/index')
        ->assertOk()
        ->assertSee('/storage/personnel/avatar.jpg');

    $this->actingAs($this->admin)
        ->get(route('personnel.profile', $personnel->personnel_id))
        ->assertOk()
        ->assertSee('/storage/personnel/avatar.jpg');

    $this->actingAs($this->admin)
        ->get(route('personnel.edit', $personnel->personnel_id))
        ->assertOk()
        ->assertSee('/storage/personnel/avatar.jpg');
});
