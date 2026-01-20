<?php

namespace Database\Seeders;

use App\Models\Muscle;
use Illuminate\Database\Seeder;

class MuscleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $muscles = [
            ['code' => 'chest', 'es' => 'Pecho', 'en' => 'Chest'],
            ['code' => 'back', 'es' => 'Espalda', 'en' => 'Back'],
            ['code' => 'shoulders', 'es' => 'Hombros', 'en' => 'Shoulders'],
            ['code' => 'biceps', 'es' => 'Bíceps', 'en' => 'Biceps'],
            ['code' => 'triceps', 'es' => 'Tríceps', 'en' => 'Triceps'],
            ['code' => 'forearms', 'es' => 'Antebrazos', 'en' => 'Forearms'],
            ['code' => 'abs', 'es' => 'Abdominales', 'en' => 'Abdominals'],
            ['code' => 'obliques', 'es' => 'Oblicuos', 'en' => 'Obliques'],
            ['code' => 'quads', 'es' => 'Cuádriceps', 'en' => 'Quadriceps'],
            ['code' => 'hamstrings', 'es' => 'Isquiotibiales', 'en' => 'Hamstrings'],
            ['code' => 'glutes', 'es' => 'Glúteos', 'en' => 'Glutes'],
            ['code' => 'calves', 'es' => 'Gemelos', 'en' => 'Calves'],
            ['code' => 'lats', 'es' => 'Dorsales', 'en' => 'Lats'],
            ['code' => 'traps', 'es' => 'Trapecios', 'en' => 'Traps'],
            ['code' => 'lower_back', 'es' => 'Zona Lumbar', 'en' => 'Lower Back'],
            ['code' => 'hip_flexors', 'es' => 'Flexores de Cadera', 'en' => 'Hip Flexors'],
            ['code' => 'adductors', 'es' => 'Aductores', 'en' => 'Adductors'],
            ['code' => 'abductors', 'es' => 'Abductores', 'en' => 'Abductors'],
        ];

        foreach ($muscles as $muscleData) {
            $muscle = Muscle::create(['code' => $muscleData['code']]);

            $muscle->translations()->createMany([
                ['locale' => 'es', 'label' => $muscleData['es']],
                ['locale' => 'en', 'label' => $muscleData['en']],
            ]);
        }
    }
}
