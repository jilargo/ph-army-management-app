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
    Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $parent = ParentUnit::create(['parent_name' => '1st Infantry Division']);
    Units::create([
        'parent_id' => $parent->parent_id,
        'unit_name' => 'Alpha Co',
        'unit_code' => 'A-CO',
        'location' => 'Camp',
        'status' => 'Active',
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@smoke.test']);

    Personnel::create([
        'user_id' => $admin->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Admin',
        'last_name' => 'User',
        'status' => 'Active',
    ]);
});

it('renders every authenticated admin page inside the app layout', function () {
    $admin = User::where('email', 'admin@smoke.test')->first();
    $personnel = Personnel::first();

    $pages = [
        '/',
        '/user-dashboard',
        '/tasks',
        '/leaves',
        '/leaves/create',
        '/promotions',
        '/personnel/create',
        '/personnel/index',
        '/units',
        '/enlistments',
        '/reports',
        "/personnel/{$personnel->personnel_id}",
        "/personnel/{$personnel->personnel_id}/edit",
    ];

    foreach ($pages as $page) {
        $this->actingAs($admin)
            ->get($page)
            ->assertStatus(200)
            ->assertSee('PH Army', escape: false);
    }
});

it('shows the unit column and filters personnel by unit', function () {
    $admin = User::where('email', 'admin@smoke.test')->first();
    $parent = ParentUnit::first();

    $bravo = Units::create([
        'parent_id' => $parent->parent_id,
        'unit_name' => 'Bravo Co',
        'unit_code' => 'B-CO',
        'location' => 'Camp',
        'status' => 'Active',
    ]);

    Personnel::create([
        'user_id' => $admin->id,
        'rank_id' => 1,
        'unit_id' => $bravo->unit_id,
        'first_name' => 'Bravo',
        'last_name' => 'Soldier',
        'status' => 'Active',
    ]);

    $this->actingAs($admin)
        ->get('/personnel/index')
        ->assertStatus(200)
        ->assertSee('All Units', escape: false)
        ->assertSee('Alpha Co', escape: false)
        ->assertSee('Bravo Co', escape: false);

    Livewire::actingAs($admin)
        ->test('personnel.index')
        ->set('unit_id', $bravo->unit_id)
        ->assertSee('Bravo', escape: false)
        ->assertDontSee('Admin User', escape: false);
});

it('shows the personnel status breakdown on the admin dashboard', function () {
    $admin = User::where('email', 'admin@smoke.test')->first();

    foreach (['Inactive', 'Retired', 'Leave'] as $status) {
        Personnel::create([
            'user_id' => $admin->id,
            'rank_id' => 1,
            'unit_id' => 1,
            'first_name' => 'Test',
            'last_name' => $status,
            'status' => $status,
        ]);
    }

    $response = $this->actingAs($admin)
        ->get('/')
        ->assertStatus(200)
        ->assertSee('Personnel Status', escape: false)
        ->assertSee('Active Soldiers', escape: false)
        ->assertSee('Inactive Soldiers', escape: false)
        ->assertSee('Retired Soldiers', escape: false)
        ->assertSee('On Leave', escape: false)
        ->assertSee('Newly Accepted Soldiers', escape: false);

    foreach (['border-l-green-500', 'border-l-red-500', 'border-l-slate-500', 'border-l-blue-500'] as $accent) {
        $this->assertStringContainsString($accent, $response->getContent());
    }
});

it('renders the soldier dashboard layout', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@smoke.test']);

    $this->actingAs($soldier)
        ->get('/user-dashboard')
        ->assertStatus(200)
        ->assertSee('PH Army', escape: false);
});

it('color-codes the personnel status badge in the index table', function () {
    $admin = User::where('email', 'admin@smoke.test')->first();

    $colors = [
        'Active' => 'bg-green-100 text-green-700',
        'Inactive' => 'bg-red-100 text-red-700',
        'Leave' => 'bg-blue-100 text-blue-700',
        'Retired' => 'bg-slate-200 text-slate-600',
    ];

    foreach ($colors as $status => $classes) {
        Personnel::create([
            'user_id' => $admin->id,
            'rank_id' => 1,
            'unit_id' => 1,
            'first_name' => 'Test',
            'last_name' => $status,
            'status' => $status,
        ]);
    }

    $response = $this->actingAs($admin)
        ->get('/personnel/index')
        ->assertStatus(200)
        ->assertSee('On Leave', escape: false);

    foreach ($colors as $classes) {
        $this->assertStringContainsString($classes, $response->getContent());
    }
});
