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
        Schema::create('exercise_instruction_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->text('setup')->nullable();
            $table->json('execution_steps')->nullable();
            $table->text('common_mistakes')->nullable();
            $table->text('cues')->nullable();
            $table->text('breathing')->nullable();
            $table->timestamps();
            $table->unique(['exercise_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_instruction_sets');
    }
};
