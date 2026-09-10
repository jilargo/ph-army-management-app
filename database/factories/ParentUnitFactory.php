<?php

namespace Database\Factories;

use App\Models\ParentUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentUnit>
 */
class ParentUnitFactory extends Factory
{
    protected $model = ParentUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_name' => fake()->unique()->randomElement([
                '1st Infantry Division',
                '2nd Infantry Division',
                '3rd Infantry Division',
            ]),
        ];
    }
}
