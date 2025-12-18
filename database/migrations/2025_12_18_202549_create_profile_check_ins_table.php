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
        Schema::create('profile_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('profile_id')->constrained('profiles')->onDelete('cascade');

            $table->decimal('weight_kg', 5, 2)->nullable();


            $table->unsignedTinyInteger('stress_level')->nullable();
            $table->unsignedTinyInteger('sleep_quality')->nullable();
            $table->unsignedTinyInteger('daily_energy')->nullable();

            $table->unsignedTinyInteger('weekly_fatigue')->nullable();
            $table->unsignedTinyInteger('routine_satisfaction')->nullable();

            $table->jsonb('context_changes')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_check_ins');
    }
};
