<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'code' => 'home_friendly',
                'translations' => [
                    'es' => 'Amigable para casa',
                    'en' => 'Home friendly',
                ],
            ],
            [
                'code' => 'rehab',
                'translations' => [
                    'es' => 'Rehabilitación',
                    'en' => 'Rehabilitation',
                ],
            ],
            [
                'code' => 'mobility',
                'translations' => [
                    'es' => 'Movilidad',
                    'en' => 'Mobility',
                ],
            ],
            [
                'code' => 'strength',
                'translations' => [
                    'es' => 'Fuerza',
                    'en' => 'Strength',
                ],
            ],
            [
                'code' => 'cardio',
                'translations' => [
                    'es' => 'Cardio',
                    'en' => 'Cardio',
                ],
            ],
            [
                'code' => 'beginner_friendly',
                'translations' => [
                    'es' => 'Principiante',
                    'en' => 'Beginner friendly',
                ],
            ],
            [
                'code' => 'advanced',
                'translations' => [
                    'es' => 'Avanzado',
                    'en' => 'Advanced',
                ],
            ],
            [
                'code' => 'low_impact',
                'translations' => [
                    'es' => 'Bajo impacto',
                    'en' => 'Low impact',
                ],
            ],
            [
                'code' => 'core_focus',
                'translations' => [
                    'es' => 'Enfoque en core',
                    'en' => 'Core focus',
                ],
            ],
            [
                'code' => 'flexibility',
                'translations' => [
                    'es' => 'Flexibilidad',
                    'en' => 'Flexibility',
                ],
            ],
            [
                'code' => 'explosive',
                'translations' => [
                    'es' => 'Explosivo',
                    'en' => 'Explosive',
                ],
            ],
            [
                'code' => 'stability',
                'translations' => [
                    'es' => 'Estabilidad',
                    'en' => 'Stability',
                ],
            ],
            [
                'code' => 'balance',
                'translations' => [
                    'es' => 'Balance',
                    'en' => 'Balance',
                ],
            ],
            [
                'code' => 'power',
                'translations' => [
                    'es' => 'Potencia',
                    'en' => 'Power',
                ],
            ],
            [
                'code' => 'endurance',
                'translations' => [
                    'es' => 'Resistencia',
                    'en' => 'Endurance',
                ],
            ],
        ];

        foreach ($tags as $tagData) {
            $tag = Tag::create(['code' => $tagData['code']]);

            foreach ($tagData['translations'] as $locale => $label) {
                $tag->translations()->create([
                    'locale' => $locale,
                    'label' => $label,
                ]);
            }
        }
    }
}
