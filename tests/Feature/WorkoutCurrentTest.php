<?php

use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can mark a workout as current', function () {
    $client = User::factory()->create();
    $trainer = User::factory()->create();

    $workout = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => false,
    ]);

    $workout->makeCurrentForClient();

    expect($workout->fresh()->is_current)->toBeTrue();
});

it('only allows one current workout per client', function () {
    $client = User::factory()->create();
    $trainer = User::factory()->create();

    $workout1 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => true,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => false,
    ]);

    // Marcar la segunda rutina como actual
    $workout2->makeCurrentForClient();

    // La primera rutina ya no debe ser actual
    expect($workout1->fresh()->is_current)->toBeFalse();
    expect($workout2->fresh()->is_current)->toBeTrue();
});

it('can get current workout for a client', function () {
    $client = User::factory()->create();
    $trainer = User::factory()->create();

    $workout1 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => false,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => true,
    ]);

    $currentWorkout = Workout::currentForClient($client->id);

    expect($currentWorkout->id)->toBe($workout2->id);
});

it('returns null when no current workout exists for client', function () {
    $client = User::factory()->create();

    $currentWorkout = Workout::currentForClient($client->id);

    expect($currentWorkout)->toBeNull();
});

it('current workout scope works correctly', function () {
    $client = User::factory()->create();
    $trainer = User::factory()->create();

    $workout1 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => false,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => true,
    ]);

    $currentWorkouts = Workout::current()->get();

    expect($currentWorkouts)->toHaveCount(1);
    expect($currentWorkouts->first()->id)->toBe($workout2->id);
});

it('for client scope works correctly', function () {
    $client1 = User::factory()->create();
    $client2 = User::factory()->create();
    $trainer = User::factory()->create();

    $workout1 = Workout::factory()->create([
        'client_id' => $client1->id,
        'trainer_id' => $trainer->id,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client2->id,
        'trainer_id' => $trainer->id,
    ]);

    $client1Workouts = Workout::forClient($client1->id)->get();

    expect($client1Workouts)->toHaveCount(1);
    expect($client1Workouts->first()->id)->toBe($workout1->id);
});

it('automatically unmarks other workouts when marking one as current', function () {
    $client = User::factory()->create();
    $trainer = User::factory()->create();

    $workout1 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => true,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => false,
    ]);

    $workout3 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'is_current' => false,
    ]);

    // Marcar workout3 como actual directamente en la base de datos
    $workout3->update(['is_current' => true]);

    // Verificar que solo workout3 está marcada como actual
    expect($workout1->fresh()->is_current)->toBeFalse();
    expect($workout2->fresh()->is_current)->toBeFalse();
    expect($workout3->fresh()->is_current)->toBeTrue();
});

it('does not affect workouts of different clients', function () {
    $client1 = User::factory()->create();
    $client2 = User::factory()->create();
    $trainer = User::factory()->create();

    $workout1 = Workout::factory()->create([
        'client_id' => $client1->id,
        'trainer_id' => $trainer->id,
        'is_current' => true,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client2->id,
        'trainer_id' => $trainer->id,
        'is_current' => true,
    ]);

    // Ambas rutinas deben seguir siendo actuales ya que son de clientes diferentes
    expect($workout1->fresh()->is_current)->toBeTrue();
    expect($workout2->fresh()->is_current)->toBeTrue();
});
