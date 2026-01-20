<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseTag>
 */
class ExerciseTagFactory extends Factory
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
            'tag_id' => \App\Models\Tag::factory(),
        ];
    }
}
