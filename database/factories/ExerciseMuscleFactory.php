<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Muscle;
use App\Models\MuscleRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseMuscle>
 */
class ExerciseMuscleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercise_id' => Exercise::factory(),
            'muscle_id' => Muscle::factory(),
            'muscle_role_id' => MuscleRole::factory(),
        ];
    }
}
