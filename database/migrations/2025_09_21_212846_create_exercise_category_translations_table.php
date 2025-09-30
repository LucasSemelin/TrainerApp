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
        Schema::create('exercise_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('exercise_categories')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('type_label')->nullable();
            $table->string('name_label')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'locale']);
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_category_translations');
    }
};
