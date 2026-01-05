<?php

namespace Database\Seeders;

use App\Models\MediaType;
use Illuminate\Database\Seeder;

class MediaTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mediaTypes = [
            [
                'code' => 'video',
                'translations' => [
                    'es' => 'Video',
                    'en' => 'Video',
                ],
            ],
            [
                'code' => 'image',
                'translations' => [
                    'es' => 'Imagen',
                    'en' => 'Image',
                ],
            ],
            [
                'code' => 'gif',
                'translations' => [
                    'es' => 'GIF',
                    'en' => 'GIF',
                ],
            ],
            [
                'code' => 'animation',
                'translations' => [
                    'es' => 'Animación',
                    'en' => 'Animation',
                ],
            ],
            [
                'code' => 'model_3d',
                'translations' => [
                    'es' => 'Modelo 3D',
                    'en' => '3D Model',
                ],
            ],
        ];

        foreach ($mediaTypes as $mediaTypeData) {
            $mediaType = MediaType::create(['code' => $mediaTypeData['code']]);

            foreach ($mediaTypeData['translations'] as $locale => $label) {
                $mediaType->translations()->create([
                    'locale' => $locale,
                    'label' => $label,
                ]);
            }
        }
    }
}
