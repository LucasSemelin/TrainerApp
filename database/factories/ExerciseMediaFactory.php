<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseMedia>
 */
class ExerciseMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providers = ['youtube', 'vimeo', null, 's3'];

        return [
            'exercise_id' => \App\Models\Exercise::factory(),
            'media_type_id' => \App\Models\MediaType::factory(),
            'url' => fake()->url(),
            'provider' => fake()->randomElement($providers),
            'is_primary' => fake()->boolean(20),
            'locale' => fake()->randomElement(['es', 'en', null]),
        ];
    }
}
