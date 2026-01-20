<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Seeder;

class WorkoutCurrentExampleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear un cliente y un entrenador
        $client = User::factory()->create(['email' => 'client'.time().'@example.com']);
        $trainer = User::factory()->create(['email' => 'trainer'.time().'@example.com']);

        // Crear algunas rutinas históricas
        $oldWorkout1 = Workout::factory()->create([
            'name' => 'Rutina de Principiante',
            'client_id' => $client->id,
            'trainer_id' => $trainer->id,
            'is_current' => false,
        ]);

        $oldWorkout2 = Workout::factory()->create([
            'name' => 'Rutina Intermedia',
            'client_id' => $client->id,
            'trainer_id' => $trainer->id,
            'is_current' => false,
        ]);

        // Crear la rutina actual
        $currentWorkout = Workout::factory()->create([
            'name' => 'Rutina Avanzada',
            'client_id' => $client->id,
            'trainer_id' => $trainer->id,
            'is_current' => true,
        ]);

        $this->command->info('✅ Creado cliente: '.$client->email);
        $this->command->info('✅ Creado entrenador: '.$trainer->email);
        $this->command->info('✅ Creadas 3 rutinas para el cliente');
        $this->command->info('✅ Rutina actual: '.$currentWorkout->name);

        // Demostrar el uso de los métodos
        $this->command->info('');
        $this->command->info('📋 Demostrando funcionalidad:');

        // Obtener rutina actual
        $current = Workout::currentForClient($client->id);
        $this->command->info("Rutina actual del cliente: {$current->name}");

        // Obtener todas las rutinas del cliente
        $allWorkouts = Workout::forClient($client->id)->get();
        $this->command->info("Total de rutinas del cliente: {$allWorkouts->count()}");

        // Cambiar la rutina actual
        $oldWorkout1->makeCurrentForClient();
        $newCurrent = Workout::currentForClient($client->id);
        $this->command->info("Nueva rutina actual: {$newCurrent->name}");
    }
}
