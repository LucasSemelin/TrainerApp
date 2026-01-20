<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TagTranslation>
 */
class TagTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag_id' => \App\Models\Tag::factory(),
            'locale' => fake()->randomElement(['es', 'en']),
            'label' => fake()->words(2, true),
        ];
    }
}
