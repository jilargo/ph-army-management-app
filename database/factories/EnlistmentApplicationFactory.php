<?php

namespace Database\Factories;

use App\Models\EnlistmentApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnlistmentApplication>
 */
class EnlistmentApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->lastName(),
            'last_name' => $this->faker->lastName(),
            'suffix' => null,
            'date_of_birth' => $this->faker->date('Y-m-d', '2002-12-31'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'contact_number' => $this->faker->numerify('0917#######'),
            'personal_email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'status' => 'pending',
        ];
    }
}
