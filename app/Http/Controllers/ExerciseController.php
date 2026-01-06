<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $query = Exercise::query();

        // Si hay búsqueda por nombre
        if ($search = $request->get('search')) {
            $query->searchByNameFlexible($search, 'es');
        }

        // Si hay filtro por grupo muscular
        if ($muscleGroup = $request->get('muscle_group')) {
            $query->withCategory('muscle_group', $muscleGroup);
        }

        $exercises = $query->with([
            'names' => function ($q) {
                $q->where('locale', 'es');
            },
            'categories.translations' => function ($q) {
                $q->where('locale', 'es');
            },
        ])->get();

        // Transformar la respuesta para incluir todos los nombres
        $exercises = $exercises->map(function ($exercise) {
            $primaryName = $exercise->names->firstWhere('is_primary', true);
            $allNames = $exercise->names->pluck('name')->toArray();

            // Agrupar categorías por tipo con sus traducciones en español
            $categoriesGrouped = $exercise->categories
                ->groupBy('type_slug')
                ->map(fn($group) => $group->map(fn($cat) => $cat->label('es'))->values()->toArray())
                ->toArray();

            return [
                'id' => $exercise->id,
                'name' => $primaryName?->name ?? $exercise->name,
                'alternative_names' => $allNames,
                'slug' => $exercise->slug,
                'description' => $exercise->description,
                'categories' => $categoriesGrouped,
            ];
        });

        // Obtener todos los grupos musculares únicos para el filtro
        $muscleGroups = \App\Models\ExerciseCategory::where('type_slug', 'muscle_group')
            ->with(['translations' => function ($q) {
                $q->where('locale', 'es');
            }])
            ->get()
            ->map(fn($cat) => [
                'slug' => $cat->name_slug,
                'label' => $cat->label('es'),
            ])
            ->sortBy('label')
            ->values();

        return Inertia::render('Exercises/ExercisesIndex', [
            'exercises' => $exercises,
            'search' => $request->get('search', ''),
            'muscleGroup' => $request->get('muscle_group', ''),
            'muscleGroups' => $muscleGroups,
        ]);
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
            },
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

    public function show(Exercise $exercise)
    {
        $exercise->load([
            'names' => function ($q) {
                $q->where('locale', 'es');
            },
            'categories.translations' => function ($q) {
                $q->where('locale', 'es');
            },
            'equipment.translations' => function ($q) {
                $q->where('locale', 'es');
            },
            'tags.translations' => function ($q) {
                $q->where('locale', 'es');
            },
            'media.mediaType.translations' => function ($q) {
                $q->where('locale', 'es');
            },
            'instructionSets.instructions',
        ]);

        $primaryName = $exercise->names->firstWhere('is_primary', true);
        $allNames = $exercise->names->pluck('name')->toArray();

        // Agrupar categorías por tipo con sus traducciones
        $categoriesGrouped = $exercise->categories
            ->groupBy('type_slug')
            ->map(fn($group) => $group->map(fn($cat) => $cat->label('es'))->values()->toArray())
            ->toArray();

        // Equipamiento con traducciones
        $equipment = $exercise->equipment->map(fn($eq) => $eq->label('es'))->toArray();

        // Tags con traducciones
        $tags = $exercise->tags->map(fn($tag) => $tag->label('es'))->toArray();

        // Media
        $media = $exercise->media->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => $m->url,
                'type' => $m->mediaType?->label('es') ?? $m->media_type_id,
                'order' => $m->order,
            ];
        })->sortBy('order')->values()->toArray();

        // Instrucciones agrupadas por sets
        $instructions = $exercise->instructionSets->map(function ($set) {
            return [
                'set_name' => $set->set_name,
                'order' => $set->order,
                'instructions' => $set->instructions->sortBy('step_number')->map(function ($inst) {
                    return [
                        'step' => $inst->step_number,
                        'text' => $inst->instruction_text,
                    ];
                })->values()->toArray(),
            ];
        })->sortBy('order')->values()->toArray();

        return Inertia::render('Exercises/ExercisesShow', [
            'exercise' => [
                'id' => $exercise->id,
                'name' => $primaryName?->name ?? $exercise->name,
                'alternative_names' => array_filter($allNames, fn($n) => $n !== ($primaryName?->name ?? $exercise->name)),
                'slug' => $exercise->slug,
                'description' => $exercise->description,
                'image_path' => $exercise->image_path,
                'categories' => $categoriesGrouped,
                'equipment' => $equipment,
                'tags' => $tags,
                'media' => $media,
                'instructions' => $instructions,
            ],
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'locale' => 'sometimes|string|in:es,en',
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
                },
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
