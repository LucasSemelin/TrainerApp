<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index()
    {
        return inertia('Exercises/Index');
    }

    public function list(Request $request)
    {
        $query = Exercise::query();

        // Si hay búsqueda por nombre
        if ($search = $request->get('search')) {
            $query->searchByNameFlexible($search, $request->get('locale', 'es'));
        }

        // Si hay filtros por categoría
        if ($category = $request->get('category')) {
            if (is_array($category) && isset($category['type'], $category['names'])) {
                $query->withCategory($category['type'], $category['names']);
            }
        }

        $exercises = $query->with([
            'names' => function ($q) use ($request) {
                $q->where('locale', $request->get('locale', 'es'));
            }
        ])->get();

        // Transformar la respuesta para incluir todos los nombres
        $exercises = $exercises->map(function ($exercise) {
            $primaryName = $exercise->names->firstWhere('is_primary', true);
            $allNames = $exercise->names->pluck('name')->toArray();

            return [
                'id' => $exercise->id,
                'name' => $primaryName?->name ?? $exercise->name,
                'alternative_names' => $allNames,
                'slug' => $exercise->slug,
                'description' => $exercise->description,
                'categories' => $exercise->categorySlugsGrouped(),
            ];
        });

        return response()->json($exercises);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'locale' => 'sometimes|string|in:es,en'
        ]);

        $exercises = Exercise::searchByNameFlexible(
            $request->q,
            $request->get('locale', 'es')
        )
            ->with([
                'names' => function ($q) use ($request) {
                    $q->where('locale', $request->get('locale', 'es'));
                },
                'categories.translations' => function ($q) {
                    $q->where('locale', 'es'); // Siempre en español para las categorías
                }
            ])
            ->limit(20)
            ->get();

        // Transformar la respuesta
        $exercises = $exercises->map(function ($exercise) {
            $primaryName = $exercise->names->firstWhere('is_primary', true);
            $allNames = $exercise->names->pluck('name')->toArray();

            // Obtener categorías principales (muscle_group principalmente)
            $mainCategories = $exercise->categories
                ->where('type_slug', 'muscle_group')
                ->map(function ($category) {
                    return $category->label('es'); // Usar el método label() para obtener la traducción
                })
                ->filter()
                ->values()
                ->toArray();

            // Si no hay muscle_group, usar movement_pattern como alternativa
            if (empty($mainCategories)) {
                $mainCategories = $exercise->categories
                    ->where('type_slug', 'movement_pattern')
                    ->map(function ($category) {
                        return $category->label('es');
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }

            return [
                'id' => $exercise->id,
                'name' => $primaryName?->name ?? $exercise->name,
                'alternative_names' => $allNames,
                'slug' => $exercise->slug,
                'description' => $exercise->description,
                'categories' => $mainCategories,
            ];
        });

        return response()->json($exercises);
    }
}
