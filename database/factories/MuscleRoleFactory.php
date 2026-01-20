<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MuscleRole>
 */
class MuscleRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['primary', 'secondary', 'stabilizer', 'synergist'];

        return [
            'code' => fake()->unique()->randomElement($roles),
        ];
    }
}
