<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/exercises.json');
        if (!File::exists($path)) {
            $this->command->warn("No se encontró $path. Usando fallback de /mnt/data si estás copiando del ejemplo.");
            $path = base_path('exercises.json'); // cambia si querés
        }

        $data = json_decode(File::get($path), true);

        foreach ($data as $row) {
            $exercise = Exercise::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'metadata' => $row['metadata'] ?? null,
                ]
            );

            // Mapear categorías (type_slug + name_slug -> id)
            $catIds = [];
            foreach ($row['categories'] as $c) {
                $cat = ExerciseCategory::firstOrCreate([
                    'type_slug' => $c['type_slug'],
                    'name_slug' => $c['name_slug'],
                ]);
                $catIds[] = $cat->id;
            }

            $exercise->categories()->syncWithoutDetaching(array_unique($catIds));
        }
    }
}
