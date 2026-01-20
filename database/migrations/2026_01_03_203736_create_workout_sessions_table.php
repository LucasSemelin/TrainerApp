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
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('workout_id')->constrained('workouts')->onDelete('cascade');
            $table->integer('session_order')->default(1);
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workout_id', 'session_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};
