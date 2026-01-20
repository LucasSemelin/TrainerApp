<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Equipment;
use App\Models\Exercise;
use App\Models\ExerciseMedia;
use App\Models\MediaType;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
                ->map(fn ($group) => $group->map(fn ($cat) => $cat->label('es'))->values()->toArray())
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
            ->map(fn ($cat) => [
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
            ->map(fn ($group) => $group->map(fn ($cat) => $cat->label('es'))->values()->toArray())
            ->toArray();

        // Equipamiento con traducciones
        $equipment = $exercise->equipment->map(fn ($eq) => $eq->label('es'))->toArray();

        // Tags con traducciones
        $tags = $exercise->tags->map(fn ($tag) => $tag->label('es'))->toArray();

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

        // Obtener todas las categorías disponibles por tipo para los modales
        $availableCategories = [
            'muscle_group' => \App\Models\ExerciseCategory::where('type_slug', 'muscle_group')
                ->with(['translations' => fn ($q) => $q->where('locale', 'es')])
                ->get()
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'slug' => $cat->name_slug,
                    'label' => $cat->label('es'),
                ])
                ->sortBy('label')
                ->values(),
            'movement_pattern' => \App\Models\ExerciseCategory::where('type_slug', 'movement_pattern')
                ->with(['translations' => fn ($q) => $q->where('locale', 'es')])
                ->get()
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'slug' => $cat->name_slug,
                    'label' => $cat->label('es'),
                ])
                ->sortBy('label')
                ->values(),
            'difficulty' => \App\Models\ExerciseCategory::where('type_slug', 'difficulty')
                ->with(['translations' => fn ($q) => $q->where('locale', 'es')])
                ->get()
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'slug' => $cat->name_slug,
                    'label' => $cat->label('es'),
                ])
                ->sortBy('label')
                ->values(),
        ];

        // Obtener equipamiento disponible
        $availableEquipment = Equipment::with(['translations' => fn ($q) => $q->where('locale', 'es')])
            ->get()
            ->map(fn ($eq) => [
                'id' => $eq->id,
                'code' => $eq->code,
                'label' => $eq->label('es'),
            ])
            ->sortBy('label')
            ->values();

        // Obtener tags disponibles
        $availableTags = Tag::with(['translations' => fn ($q) => $q->where('locale', 'es')])
            ->get()
            ->map(fn ($tag) => [
                'id' => $tag->id,
                'code' => $tag->code,
                'label' => $tag->label('es'),
            ])
            ->sortBy('label')
            ->values();

        // Obtener tipos de media disponibles
        $availableMediaTypes = MediaType::with(['translations' => fn ($q) => $q->where('locale', 'es')])
            ->get()
            ->map(fn ($type) => [
                'id' => $type->id,
                'code' => $type->code,
                'label' => $type->label('es'),
            ])
            ->sortBy('label')
            ->values();

        // Obtener instrucciones actuales para el locale 'es'
        $currentInstructions = $exercise->instructionSets()
            ->where('locale', 'es')
            ->first();

        // IDs actuales de equipamiento y tags para el formulario
        $currentEquipmentIds = $exercise->equipment->pluck('id')->toArray();
        $currentTagIds = $exercise->tags->pluck('id')->toArray();

        return Inertia::render('Exercises/ExercisesShow', [
            'exercise' => [
                'id' => $exercise->id,
                'name' => $primaryName?->name ?? $exercise->name,
                'alternative_names' => array_values(array_filter($allNames, fn ($n) => $n !== ($primaryName?->name ?? $exercise->name))),
                'slug' => $exercise->slug,
                'description' => $exercise->description,
                'image_path' => $exercise->image_path,
                'categories' => $categoriesGrouped,
                'equipment' => $equipment,
                'tags' => $tags,
                'media' => $media,
                'instructions' => $instructions,
                'currentEquipmentIds' => $currentEquipmentIds,
                'currentTagIds' => $currentTagIds,
            ],
            'availableCategories' => $availableCategories,
            'availableEquipment' => $availableEquipment,
            'availableTags' => $availableTags,
            'availableMediaTypes' => $availableMediaTypes,
            'currentInstructions' => $currentInstructions ? [
                'setup' => $currentInstructions->setup,
                'execution_steps' => $currentInstructions->execution_steps ?? [],
                'common_mistakes' => $currentInstructions->common_mistakes,
                'cues' => $currentInstructions->cues,
                'breathing' => $currentInstructions->breathing,
            ] : null,
        ]);
    }

    public function updateCategories(Request $request, Exercise $exercise)
    {
        $request->validate([
            'type' => 'required|string|in:muscle_group,movement_pattern,difficulty',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:exercise_categories,id',
        ]);

        // Obtener las categorías actuales del ejercicio del tipo especificado
        $currentCategories = $exercise->categories()
            ->where('type_slug', $request->type)
            ->pluck('exercise_categories.id')
            ->toArray();

        // Remover las categorías del tipo especificado
        $exercise->categories()->detach($currentCategories);

        // Agregar las nuevas categorías
        $exercise->categories()->attach($request->category_ids);

        return back()->with('success', 'Categorías actualizadas correctamente');
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        Log::info('Update method called', [
            'user_id' => auth()->id(),
            'exercise_id' => $exercise->id,
            'request_data' => $request->all(),
        ]);

        $locale = 'es'; // Default locale for the app

        // Update exercise basic info
        $exercise->update([
            'description' => $request->description,
            'image_path' => $request->image_path,
        ]);

        // Update primary name
        $primaryName = $exercise->names()
            ->where('locale', $locale)
            ->where('is_primary', true)
            ->first();

        if ($primaryName) {
            // Only update if name actually changed
            if ($primaryName->name !== $request->name) {
                $primaryName->update(['name' => $request->name]);
            }
        } else {
            // Create primary name if it doesn't exist
            $exercise->names()->create([
                'locale' => $locale,
                'name' => $request->name,
                'is_primary' => true,
            ]);
        }

        // Handle alternative names
        // Remove all non-primary names for this locale
        $exercise->names()
            ->where('locale', $locale)
            ->where('is_primary', false)
            ->delete();

        // Add new alternative names
        if ($request->alternative_names) {
            foreach ($request->alternative_names as $altName) {
                if (! empty($altName) && $altName !== $request->name) {
                    $exercise->names()->create([
                        'locale' => $locale,
                        'name' => $altName,
                        'is_primary' => false,
                    ]);
                }
            }
        }

        return back()->with('success', 'Ejercicio actualizado correctamente');
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

    public function store(StoreExerciseRequest $request)
    {
        $exercise = Exercise::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('exercises.show', $exercise);
    }

    public function destroy(Exercise $exercise)
    {
        $this->authorize('delete', $exercise);
        $exercise->delete();

        return redirect()->route('exercises.index');
    }

    public function updateEquipment(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $validated = $request->validate([
            'equipment_ids' => 'array',
            'equipment_ids.*' => 'exists:equipment,id',
        ]);

        $exercise->equipment()->sync($validated['equipment_ids'] ?? []);

        return back();
    }

    public function updateTags(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $validated = $request->validate([
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $exercise->tags()->sync($validated['tag_ids'] ?? []);

        return back();
    }

    public function storeMedia(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $validated = $request->validate([
            'url' => 'required|url|max:2000',
            'media_type_id' => 'required|exists:media_types,id',
            'provider' => 'nullable|in:youtube,vimeo,s3',
            'is_primary' => 'boolean',
        ]);

        if ($request->is_primary) {
            $exercise->media()->update(['is_primary' => false]);
        }

        $exercise->media()->create($validated + ['locale' => 'es']);

        return back();
    }

    public function destroyMedia(Exercise $exercise, ExerciseMedia $media)
    {
        $this->authorize('update', $exercise);
        abort_unless($media->exercise_id === $exercise->id, 404);
        $media->delete();

        return back();
    }

    public function storeOrUpdateInstructions(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $validated = $request->validate([
            'setup' => 'nullable|string|max:5000',
            'execution_steps' => 'nullable|array',
            'execution_steps.*' => 'string|max:1000',
            'common_mistakes' => 'nullable|string|max:5000',
            'cues' => 'nullable|string|max:5000',
            'breathing' => 'nullable|string|max:2000',
        ]);

        $exercise->instructionSets()->updateOrCreate(
            ['locale' => 'es'],
            $validated
        );

        return back();
    }
}
