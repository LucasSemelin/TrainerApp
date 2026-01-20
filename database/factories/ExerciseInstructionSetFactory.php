<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseInstructionSet>
 */
class ExerciseInstructionSetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercise_id' => \App\Models\Exercise::factory(),
            'locale' => fake()->randomElement(['es', 'en']),
            'setup' => fake()->paragraph(),
            'execution_steps' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
            'common_mistakes' => fake()->paragraph(),
            'cues' => fake()->sentence(),
            'breathing' => fake()->sentence(),
        ];
    }
}
