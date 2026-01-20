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
        Schema::create('movement_pattern_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_pattern_id')->constrained()->onDelete('cascade');
            $table->string('locale');
            $table->string('label');
            $table->timestamps();

            $table->unique(['movement_pattern_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_pattern_translations');
    }
};
