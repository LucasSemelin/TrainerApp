<?php

namespace Database\Seeders;

use App\Models\ExerciseCategory;
use App\Models\ExerciseCategoryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ExerciseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/categories.json');
        if (! File::exists($path)) {
            $this->command->warn("No se encontró $path. Usando fallback de /mnt/data si estás copiando del ejemplo.");
            $path = base_path('categories.json'); // cambia si querés
        }
        $data = json_decode(File::get($path), true);

        foreach ($data as $typeSlug => $def) {
            $values = $def['values'] ?? [];
            $typeLabels = $def['labels'] ?? [];
            $valueLabels = $def['value_labels'] ?? [];

            foreach ($values as $nameSlug) {
                $cat = ExerciseCategory::firstOrCreate([
                    'type_slug' => $typeSlug,
                    'name_slug' => $nameSlug,
                ]);

                foreach (['es', 'en'] as $locale) {
                    $tlType = $typeLabels[$locale] ?? ucfirst(str_replace('_', ' ', $typeSlug));
                    $tlName = $valueLabels[$locale][$nameSlug] ?? ucfirst(str_replace('_', ' ', $nameSlug));

                    ExerciseCategoryTranslation::updateOrCreate(
                        ['category_id' => $cat->id, 'locale' => $locale],
                        ['type_label' => $tlType, 'name_label' => $tlName]
                    );
                }
            }
        }
    }
}
