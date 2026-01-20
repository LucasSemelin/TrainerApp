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
        Schema::create('mechanic_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mechanic_id')->constrained()->onDelete('cascade');
            $table->string('locale');
            $table->string('label');
            $table->timestamps();

            $table->unique(['mechanic_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mechanic_translations');
    }
};
