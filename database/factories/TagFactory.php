<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tags = [
            'home_friendly',
            'rehab',
            'mobility',
            'strength',
            'cardio',
            'beginner_friendly',
            'advanced',
            'low_impact',
            'core_focus',
            'flexibility',
        ];

        return [
            'code' => fake()->unique()->randomElement($tags),
        ];
    }
}
