<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $equipment = ['barbell', 'dumbbell', 'cable', 'machine', 'bands', 'bodyweight', 'kettlebell', 'smith_machine', 'ez_bar'];

        return [
            'code' => fake()->unique()->randomElement($equipment),
        ];
    }
}
