<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseName;
use Illuminate\Database\Seeder;

class ExerciseNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exerciseNames = [
            'bench-press' => [
                'es' => [
                    'Press de banca plana con barra' => true, // nombre primario
                    'Press de pecho plano',
                    'Press de pecho con barra',
                    'Banco plano con barra',
                    'Press banca',
                    'Press de banca'
                ],
                'en' => [
                    'Bench Press' => true,
                    'Flat Bench Press',
                    'Barbell Bench Press',
                    'Chest Press'
                ]
            ],
            'squat' => [
                'es' => [
                    'Sentadilla con barra' => true,
                    'Squat con barra',
                    'Sentadilla trasera',
                    'Back squat',
                    'Sentadilla'
                ],
                'en' => [
                    'Back Squat' => true,
                    'Barbell Squat',
                    'Squat'
                ]
            ],
            'deadlift' => [
                'es' => [
                    'Peso muerto con barra' => true,
                    'Deadlift',
                    'Peso muerto convencional',
                    'Levantamiento de peso muerto'
                ],
                'en' => [
                    'Deadlift' => true,
                    'Conventional Deadlift',
                    'Barbell Deadlift'
                ]
            ],
            'pull-up' => [
                'es' => [
                    'Dominadas' => true,
                    'Pull-ups',
                    'Dominadas en barra',
                    'Jalones en barra fija'
                ],
                'en' => [
                    'Pull-ups' => true,
                    'Chin-ups',
                    'Bar Pull-ups'
                ]
            ],
            'push-up' => [
                'es' => [
                    'Flexiones de pecho' => true,
                    'Lagartijas',
                    'Push-ups',
                    'Flexiones de brazos'
                ],
                'en' => [
                    'Push-ups' => true,
                    'Press-ups'
                ]
            ]
        ];

        foreach ($exerciseNames as $slug => $locales) {
            $exercise = Exercise::where('slug', $slug)->first();

            if (!$exercise) {
                // Si no existe el ejercicio, lo creamos
                $exercise = Exercise::create([
                    'slug' => $slug,
                    'name' => array_keys($locales['es'])[0], // Primer nombre en español
                    'description' => 'Ejercicio creado por seeder'
                ]);
            }

            foreach ($locales as $locale => $names) {
                foreach ($names as $name => $isPrimary) {
                    if (is_numeric($name)) {
                        $name = $isPrimary;
                        $isPrimary = false;
                    }

                    ExerciseName::updateOrCreate(
                        [
                            'exercise_id' => $exercise->id,
                            'name' => $name,
                            'locale' => $locale
                        ],
                        [
                            'is_primary' => $isPrimary === true
                        ]
                    );
                }
            }
        }
    }
}
