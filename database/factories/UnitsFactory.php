<?php

namespace Database\Factories;

use App\Models\ParentUnit;
use App\Models\units;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<units>
 */
class UnitsFactory extends Factory
{
    protected $model = units::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => ParentUnit::factory(),
            'unit_name' => fake()->company(),
            'unit_code' => strtoupper(fake()->unique()->bothify('UNIT-###')),
            'location' => fake()->city(),
            'status' => 'Active',
        ];
    }
}
