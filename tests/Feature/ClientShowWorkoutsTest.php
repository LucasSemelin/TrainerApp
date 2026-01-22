<?php

use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows client workouts on the show page', function () {
    $trainer = User::factory()->create();
    $client = User::factory()->create();

    // Establecer relación trainer-cliente
    $trainer->clients()->attach($client->id, ['status' => 'accepted']);

    // Crear rutinas para el cliente
    $workout1 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'name' => 'Rutina de Fuerza',
        'status' => Workout::STATUS_ACTIVE,
    ]);

    $workout2 = Workout::factory()->create([
        'client_id' => $client->id,
        'trainer_id' => $trainer->id,
        'name' => 'Rutina de Hipertrofia',
        'status' => Workout::STATUS_DRAFT,
    ]);

    $response = $this->actingAs($trainer)->get(route('clients.show', $client));

    $response->assertInertia(function ($page) use ($workout1, $workout2) {
        $page->component('Clients/PageShow')
            ->has('client.my_workouts', 2)
            ->where('client.my_workouts.0.name', $workout1->name)
            ->where('client.my_workouts.0.status', Workout::STATUS_ACTIVE)
            ->where('client.my_workouts.1.name', $workout2->name)
            ->where('client.my_workouts.1.status', Workout::STATUS_DRAFT);
    });
});

it('shows empty state when client has no workouts', function () {
    $trainer = User::factory()->create();
    $client = User::factory()->create();

    $trainer->clients()->attach($client->id, ['status' => 'accepted']);

    $response = $this->actingAs($trainer)->get(route('clients.show', $client));

    $response->assertInertia(function ($page) {
        $page->component('Clients/PageShow')
            ->has('client')
            ->where('client.my_workouts', []);
    });
});
