<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseName>
 */
class ExerciseNameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $exerciseNames = [
            'Press de banca',
            'Sentadilla',
            'Peso muerto',
            'Press militar',
            'Dominadas',
            'Fondos',
            'Remo con barra',
            'Curl de bíceps',
        ];

        return [
            'name' => $this->faker->randomElement($exerciseNames),
            'locale' => 'es',
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function english(): static
    {
        $englishNames = [
            'Bench Press',
            'Squat',
            'Deadlift',
            'Military Press',
            'Pull-ups',
            'Dips',
            'Barbell Row',
            'Bicep Curl',
        ];

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($englishNames),
            'locale' => 'en',
        ]);
    }
}
