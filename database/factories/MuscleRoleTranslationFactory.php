<?php

namespace Database\Factories;

use App\Models\MuscleRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MuscleRoleTranslation>
 */
class MuscleRoleTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'muscle_role_id' => MuscleRole::factory(),
            'locale' => fake()->randomElement(['es', 'en']),
            'label' => fake()->word(),
        ];
    }
}
