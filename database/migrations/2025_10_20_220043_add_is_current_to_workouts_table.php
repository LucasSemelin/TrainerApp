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
        Schema::table('workouts', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('client_id');

            // Crear un índice compuesto para optimizar búsquedas de rutina actual por cliente
            $table->index(['client_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'is_current']);
            $table->dropColumn('is_current');
        });
    }
};
