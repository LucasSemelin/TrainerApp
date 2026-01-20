<?php

namespace Database\Factories;

use App\Models\Muscle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MuscleTranslation>
 */
class MuscleTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'muscle_id' => Muscle::factory(),
            'locale' => fake()->randomElement(['es', 'en']),
            'label' => fake()->words(2, true),
        ];
    }
}
