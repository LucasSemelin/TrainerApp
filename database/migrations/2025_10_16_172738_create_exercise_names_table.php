<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exercise_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_normalized')->index(); // Para búsquedas más eficientes
            $table->string('locale', 5)->default('es');
            $table->boolean('is_primary')->default(false); // Nombre principal
            $table->timestamps();

            // Índices para optimizar búsquedas
            $table->index(['exercise_id', 'is_primary']);
            $table->index(['name_normalized', 'locale']);
            $table->unique(['exercise_id', 'name', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_names');
    }
};
