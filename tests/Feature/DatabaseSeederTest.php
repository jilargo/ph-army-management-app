<?php

use App\Models\ParentUnit;
use App\Models\Units;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the Philippine Army unit structure without errors', function () {
    $this->seed();

    expect(ParentUnit::count())->toBeGreaterThan(5)
        ->and(Units::count())->toBeGreaterThan(10)
        ->and(Units::query()->distinct('unit_code')->count())->toBe(Units::count())
        ->and(Units::whereHas('parent')->count())->toBe(Units::count());
});
