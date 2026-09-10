<?php

use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Promotions;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->privateRank = Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);
    $this->captainRank = Ranks::create(['rank_name' => 'Captain', 'abbreviation' => 'CPT', 'level' => 12]);

    $parent = ParentUnit::create(['parent_name' => '1st Infantry Division']);
    Units::create(['parent_id' => $parent->parent_id, 'unit_name' => 'Alpha Co', 'unit_code' => 'A-CO', 'location' => 'Camp', 'status' => 'Active']);

    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@promo.test']);

    $this->captain = User::factory()->create(['role' => 'user', 'email' => 'captain@promo.test']);
    Personnel::create([
        'user_id' => $this->captain->id,
        'rank_id' => $this->captainRank->rank_id,
        'unit_id' => 1,
        'first_name' => 'Carlos',
        'last_name' => 'Capitan',
        'status' => 'Active',
    ]);

    $this->private = User::factory()->create(['role' => 'user', 'email' => 'private@promo.test']);
    Personnel::create([
        'user_id' => $this->private->id,
        'rank_id' => $this->privateRank->rank_id,
        'unit_id' => 1,
        'first_name' => 'Paolo',
        'last_name' => 'Private',
        'status' => 'Active',
    ]);

    $this->target = User::factory()->create(['role' => 'user', 'email' => 'target@promo.test']);
    Personnel::create([
        'user_id' => $this->target->id,
        'rank_id' => $this->privateRank->rank_id,
        'unit_id' => 1,
        'first_name' => 'Tomas',
        'last_name' => 'Target',
        'status' => 'Active',
    ]);
});

it('lets an officer (Captain and above) open the promotion page and recommend', function () {
    $this->actingAs($this->captain)
        ->get('/promotions')
        ->assertStatus(200)
        ->assertSee('Recommend Promotion', escape: false);

    $target = Personnel::where('user_id', $this->target->id)->first();

    Livewire::actingAs($this->captain)
        ->test('promotions.index')
        ->call('openCreate')
        ->assertSet('showCreateModal', true)
        ->set('personnel_id', (string) $target->personnel_id)
        ->set('from_rank_id', (string) $this->privateRank->rank_id)
        ->set('to_rank_id', (string) $this->captainRank->rank_id)
        ->set('promotion_date', now()->addMonths(2)->toDateString())
        ->set('recommendation', 'Consistent performer.')
        ->call('storePromotion')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('promotions', [
        'personnel_id' => $target->personnel_id,
        'status' => 'pending',
        'recommended_by' => $this->captain->id,
    ]);
});

it('does not let a non-officer recommend a promotion', function () {
    Livewire::actingAs($this->private)
        ->test('promotions.index')
        ->call('openCreate')
        ->assertForbidden();
});

it('does not let a soldier recommend themselves', function () {
    $captainPersonnel = Personnel::where('user_id', $this->captain->id)->first();

    Livewire::actingAs($this->captain)
        ->test('promotions.index')
        ->call('openCreate')
        ->set('personnel_id', (string) $captainPersonnel->personnel_id)
        ->set('from_rank_id', (string) $this->captainRank->rank_id)
        ->set('to_rank_id', (string) $this->privateRank->rank_id)
        ->call('storePromotion')
        ->assertHasErrors(['personnel_id']);
});

it('does not let a soldier approve a recommendation', function () {
    $target = Personnel::where('user_id', $this->target->id)->first();
    $promotion = Promotions::create([
        'personnel_id' => $target->personnel_id,
        'from_rank_id' => $this->privateRank->rank_id,
        'to_rank_id' => $this->captainRank->rank_id,
        'promotion_date' => now()->addMonths(2)->toDateString(),
        'status' => 'pending',
        'remarks' => '',
        'recommended_by' => $this->admin->id,
    ]);

    Livewire::actingAs($this->captain)
        ->test('promotions.index')
        ->call('viewPromotion', $promotion->promotion_id)
        ->call('approve')
        ->assertForbidden();
});

it('does not let a soldier reject a recommendation', function () {
    $target = Personnel::where('user_id', $this->target->id)->first();
    $promotion = Promotions::create([
        'personnel_id' => $target->personnel_id,
        'from_rank_id' => $this->privateRank->rank_id,
        'to_rank_id' => $this->captainRank->rank_id,
        'promotion_date' => now()->addMonths(2)->toDateString(),
        'status' => 'pending',
        'remarks' => '',
        'recommended_by' => $this->admin->id,
    ]);

    Livewire::actingAs($this->captain)
        ->test('promotions.index')
        ->call('viewPromotion', $promotion->promotion_id)
        ->call('reject')
        ->assertForbidden();
});

it('still lets the admin approve a recommendation and update the rank', function () {
    $target = Personnel::where('user_id', $this->target->id)->first();
    $promotion = Promotions::create([
        'personnel_id' => $target->personnel_id,
        'from_rank_id' => $this->privateRank->rank_id,
        'to_rank_id' => $this->captainRank->rank_id,
        'promotion_date' => now()->addMonths(2)->toDateString(),
        'status' => 'pending',
        'remarks' => '',
        'recommended_by' => $this->captain->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test('promotions.index')
        ->call('viewPromotion', $promotion->promotion_id)
        ->call('approve')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('promotions', [
        'promotion_id' => $promotion->promotion_id,
        'status' => 'approved',
        'approved_by' => $this->admin->id,
    ]);

    expect($target->fresh()->rank_id)->toBe($this->captainRank->rank_id);
});

it('does not expose the recommend button or actions to a non-admin spot check', function () {
    $this->actingAs($this->private)
        ->get('/promotions')
        ->assertStatus(200)
        ->assertDontSee('Recommend Promotion');
});
