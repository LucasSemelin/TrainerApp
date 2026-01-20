<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name, // Keep name field (required NOT NULL)
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'image_path' => fake()->optional()->imageUrl(),
        ];
    }
}
