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
        Schema::create('exercise_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->uuid('media_type_id');
            $table->foreign('media_type_id')->references('id')->on('media_types')->cascadeOnDelete();
            $table->text('url');
            $table->text('provider')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('locale', 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_media');
    }
};
