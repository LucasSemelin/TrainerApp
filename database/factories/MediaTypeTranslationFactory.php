<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaTypeTranslation>
 */
class MediaTypeTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'media_type_id' => \App\Models\MediaType::factory(),
            'locale' => fake()->randomElement(['es', 'en']),
            'label' => fake()->word(),
        ];
    }
}
