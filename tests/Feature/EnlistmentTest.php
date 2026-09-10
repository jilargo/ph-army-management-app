<?php

use App\Models\EnlistmentApplication;
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

    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@enlist.test']);
    $this->soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@enlist.test']);

    $this->rank = Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $formation = ParentUnit::create(['parent_name' => '1st Infantry (Tabak) Division']);

    $this->unit = Units::create([
        'parent_id' => $formation->parent_id,
        'unit_name' => '1st Infantry Battalion',
        'unit_code' => '1IB',
        'location' => 'Camp',
        'status' => 'Active',
    ]);
});

it('lets the public submit an enlistment application with document requirements', function () {
    Livewire::test('applicant.apply')
        ->set('first_name', 'Juan')
        ->set('middle_name', 'Santos')
        ->set('last_name', 'Dela Cruz')
        ->set('date_of_birth', '2000-01-01')
        ->set('gender', 'Male')
        ->set('contact_number', '09170000000')
        ->set('personal_email', 'juan.applicant@example.com')
        ->set('address', 'Manila')
        ->set('documents', [
            UploadedFile::fake()->image('nbi-clearance.jpg'),
            UploadedFile::fake()->create('birth-certificate.pdf', 200, 'application/pdf'),
        ])
        ->call('submit')
        ->assertHasNoErrors();

    $application = EnlistmentApplication::where('personal_email', 'juan.applicant@example.com')->first();
    expect($application)->not->toBeNull()
        ->and($application->status)->toBe('pending')
        ->and($application->documents()->count())->toBe(2);
});

it('requires at least one requirement document', function () {
    Livewire::test('applicant.apply')
        ->set('first_name', 'Juan')
        ->set('last_name', 'Dela Cruz')
        ->set('date_of_birth', '2000-01-01')
        ->set('gender', 'Male')
        ->set('contact_number', '09170000000')
        ->set('personal_email', 'juan.applicant@example.com')
        ->set('address', 'Manila')
        ->call('submit')
        ->assertHasErrors(['documents']);
});

it('lets an admin accept an applicant and enlist them as a soldier', function () {
    $application = EnlistmentApplication::factory()->create([
        'first_name' => 'Maria',
        'last_name' => 'Clara',
        'personal_email' => 'maria.applicant@example.com',
    ]);

    Livewire::actingAs($this->admin)
        ->test('enlistments.index')
        ->call('viewApplication', $application->id)
        ->set('acceptRankId', (string) $this->rank->rank_id)
        ->set('acceptUnitId', (string) $this->unit->unit_id)
        ->call('accept')
        ->assertHasNoErrors();

    expect($application->fresh()->status)->toBe('accepted')
        ->and($application->fresh()->personnel_id)->not->toBeNull()
        ->and($application->fresh()->reviewed_by)->toBe($this->admin->id);

    $this->assertDatabaseHas('personnels', [
        'first_name' => 'Maria',
        'last_name' => 'Clara',
        'rank_id' => $this->rank->rank_id,
        'unit_id' => $this->unit->unit_id,
        'status' => 'Active',
        'date_of_entry' => now()->toDateString(),
    ]);
});

it('does not enlist without choosing a rank and unit', function () {
    $application = EnlistmentApplication::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('enlistments.index')
        ->call('viewApplication', $application->id)
        ->call('accept')
        ->assertHasErrors(['acceptRankId', 'acceptUnitId']);

    expect($application->fresh()->status)->toBe('pending')
        ->and(Personnel::count())->toBe(0);
});

it('lets an admin reject an application with a reason', function () {
    $application = EnlistmentApplication::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('enlistments.index')
        ->call('viewApplication', $application->id)
        ->set('remarks', 'Does not meet the age requirement.')
        ->call('reject')
        ->assertHasNoErrors();

    expect($application->fresh()->status)->toBe('rejected')
        ->and($application->fresh()->remarks)->toBe('Does not meet the age requirement.')
        ->and(Personnel::count())->toBe(0);
});

it('requires a reason when rejecting', function () {
    $application = EnlistmentApplication::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('enlistments.index')
        ->call('viewApplication', $application->id)
        ->call('reject')
        ->assertHasErrors(['remarks']);
});

it('does not let a soldier open the enlistment page or decide on applications', function () {
    $application = EnlistmentApplication::factory()->create();

    $this->actingAs($this->soldier)
        ->get('/enlistments')
        ->assertForbidden();

    Livewire::actingAs($this->soldier)
        ->test('enlistments.index')
        ->call('viewApplication', $application->id)
        ->call('accept')
        ->assertForbidden();

    Livewire::actingAs($this->soldier)
        ->test('enlistments.index')
        ->call('viewApplication', $application->id)
        ->call('reject')
        ->assertForbidden();
});
