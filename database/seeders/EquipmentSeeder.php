<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = [
            ['code' => 'barbell', 'es' => 'Barra', 'en' => 'Barbell'],
            ['code' => 'dumbbell', 'es' => 'Mancuernas', 'en' => 'Dumbbell'],
            ['code' => 'cable', 'es' => 'Polea', 'en' => 'Cable'],
            ['code' => 'machine', 'es' => 'Máquina', 'en' => 'Machine'],
            ['code' => 'bands', 'es' => 'Bandas Elásticas', 'en' => 'Resistance Bands'],
            ['code' => 'bodyweight', 'es' => 'Peso Corporal', 'en' => 'Bodyweight'],
            ['code' => 'kettlebell', 'es' => 'Pesa Rusa', 'en' => 'Kettlebell'],
            ['code' => 'smith_machine', 'es' => 'Máquina Smith', 'en' => 'Smith Machine'],
            ['code' => 'ez_bar', 'es' => 'Barra Z', 'en' => 'EZ Bar'],
            ['code' => 'trap_bar', 'es' => 'Barra Hexagonal', 'en' => 'Trap Bar'],
            ['code' => 'suspension', 'es' => 'Suspensión (TRX)', 'en' => 'Suspension Trainer'],
            ['code' => 'medicine_ball', 'es' => 'Balón Medicinal', 'en' => 'Medicine Ball'],
            ['code' => 'foam_roller', 'es' => 'Rodillo de Espuma', 'en' => 'Foam Roller'],
        ];

        foreach ($equipment as $equipmentData) {
            $equipmentModel = Equipment::create(['code' => $equipmentData['code']]);

            $equipmentModel->translations()->createMany([
                ['locale' => 'es', 'label' => $equipmentData['es']],
                ['locale' => 'en', 'label' => $equipmentData['en']],
            ]);
        }
    }
}
