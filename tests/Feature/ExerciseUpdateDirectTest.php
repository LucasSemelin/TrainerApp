<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\ExerciseName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('update exercise basic information', function () {
    $exercise = Exercise::factory()->create([
        'description' => 'Original description',
        'image_path' => 'https://example.com/old.jpg',
    ]);

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Nombre Original',
        'is_primary' => true,
    ]);

    $request = UpdateExerciseRequest::create(
        "/exercises/{$exercise->id}",
        'PATCH',
        [
            'name' => 'Nombre Actualizado',
            'description' => 'Updated description',
            'image_path' => 'https://example.com/new.jpg',
            'alternative_names' => [],
        ]
    );

    $controller = new ExerciseController;
    $response = $controller->update($request, $exercise);

    expect($response)->toBeInstanceOf(RedirectResponse::class);

    $exercise->refresh();
    expect($exercise->description)->toBe('Updated description');
    expect($exercise->image_path)->toBe('https://example.com/new.jpg');

    $primaryName = $exercise->names()->where('is_primary', true)->first();
    expect($primaryName->name)->toBe('Nombre Actualizado');
});

test('add alternative names to exercise', function () {
    $exercise = Exercise::factory()->create();

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Press Banca',
        'is_primary' => true,
    ]);

    $request = UpdateExerciseRequest::create(
        "/exercises/{$exercise->id}",
        'PATCH',
        [
            'name' => 'Press Banca',
            'description' => $exercise->description,
            'image_path' => $exercise->image_path,
            'alternative_names' => ['Bench Press', 'Press de Pecho'],
        ]
    );

    $controller = new ExerciseController;
    $controller->update($request, $exercise);

    $exercise->refresh();
    $alternativeNames = $exercise->names()
        ->where('locale', 'es')
        ->where('is_primary', false)
        ->pluck('name')
        ->toArray();

    expect($alternativeNames)->toHaveCount(2);
    expect($alternativeNames)->toContain('Bench Press');
    expect($alternativeNames)->toContain('Press de Pecho');
});

test('remove alternative names from exercise', function () {
    $exercise = Exercise::factory()->create();

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Press Banca',
        'is_primary' => true,
    ]);

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Bench Press',
        'is_primary' => false,
    ]);

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Press de Pecho',
        'is_primary' => false,
    ]);

    $request = UpdateExerciseRequest::create(
        "/exercises/{$exercise->id}",
        'PATCH',
        [
            'name' => 'Press Banca',
            'description' => $exercise->description,
            'image_path' => $exercise->image_path,
            'alternative_names' => [],
        ]
    );

    $controller = new ExerciseController;
    $controller->update($request, $exercise);

    $exercise->refresh();
    $alternativeCount = $exercise->names()
        ->where('locale', 'es')
        ->where('is_primary', false)
        ->count();

    expect($alternativeCount)->toBe(0);
});

test('require name field', function () {
    $exercise = Exercise::factory()->create();

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Original Name',
        'is_primary' => true,
    ]);

    expect(function () use ($exercise) {
        $request = UpdateExerciseRequest::create(
            "/exercises/{$exercise->id}",
            'PATCH',
            [
                'name' => '',
                'description' => 'Some description',
            ]
        );
        $request->validateResolved();
    })->toThrow(ValidationException::class);
});

test('validate unique exercise name', function () {
    // Create existing exercise with a name
    $existingExercise = Exercise::factory()->create();
    ExerciseName::factory()->create([
        'exercise_id' => $existingExercise->id,
        'locale' => 'es',
        'name' => 'Press Militar',
        'is_primary' => true,
    ]);

    // Create exercise we want to update
    $exercise = Exercise::factory()->create();
    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Press Banca',
        'is_primary' => true,
    ]);

    expect(function () use ($exercise) {
        $request = UpdateExerciseRequest::create(
            "/exercises/{$exercise->id}",
            'PATCH',
            [
                'name' => 'Press Militar', // Try to use existing name
                'description' => 'Some description',
            ]
        );
        $request->validateResolved();
    })->toThrow(ValidationException::class);
});

test('allow keeping same name when updating', function () {
    $exercise = Exercise::factory()->create();

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Press Banca',
        'is_primary' => true,
    ]);

    $request = UpdateExerciseRequest::create(
        "/exercises/{$exercise->id}",
        'PATCH',
        [
            'name' => 'Press Banca', // Same name
            'description' => 'Updated description',
        ]
    );

    $controller = new ExerciseController;
    $response = $controller->update($request, $exercise);

    expect($response)->toBeInstanceOf(RedirectResponse::class);

    $exercise->refresh();
    $primaryName = $exercise->names()->where('is_primary', true)->first();
    expect($primaryName->name)->toBe('Press Banca');
});

test('validate image_path is valid URL', function () {
    $exercise = Exercise::factory()->create();

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Original Name',
        'is_primary' => true,
    ]);

    expect(function () use ($exercise) {
        $request = UpdateExerciseRequest::create(
            "/exercises/{$exercise->id}",
            'PATCH',
            [
                'name' => 'Valid Name',
                'image_path' => 'not-a-valid-url',
            ]
        );
        $request->validateResolved();
    })->toThrow(ValidationException::class);
});

test('validate alternative names are distinct', function () {
    $exercise = Exercise::factory()->create();

    ExerciseName::factory()->create([
        'exercise_id' => $exercise->id,
        'locale' => 'es',
        'name' => 'Original Name',
        'is_primary' => true,
    ]);

    expect(function () use ($exercise) {
        $request = UpdateExerciseRequest::create(
            "/exercises/{$exercise->id}",
            'PATCH',
            [
                'name' => 'Valid Name',
                'alternative_names' => ['Bench Press', 'Bench Press'], // Duplicate
            ]
        );
        $request->validateResolved();
    })->toThrow(ValidationException::class);
});
