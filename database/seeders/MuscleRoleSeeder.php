<?php

namespace Database\Seeders;

use App\Models\MuscleRole;
use Illuminate\Database\Seeder;

class MuscleRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['code' => 'primary', 'es' => 'Principal', 'en' => 'Primary'],
            ['code' => 'secondary', 'es' => 'Secundario', 'en' => 'Secondary'],
            ['code' => 'stabilizer', 'es' => 'Estabilizador', 'en' => 'Stabilizer'],
            ['code' => 'synergist', 'es' => 'Sinergista', 'en' => 'Synergist'],
        ];

        foreach ($roles as $roleData) {
            $role = MuscleRole::create(['code' => $roleData['code']]);

            $role->translations()->createMany([
                ['locale' => 'es', 'label' => $roleData['es']],
                ['locale' => 'en', 'label' => $roleData['en']],
            ]);
        }
    }
}
