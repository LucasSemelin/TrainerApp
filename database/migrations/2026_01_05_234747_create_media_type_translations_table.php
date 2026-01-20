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
        Schema::create('media_type_translations', function (Blueprint $table) {
            $table->id();
            $table->uuid('media_type_id');
            $table->foreign('media_type_id')->references('id')->on('media_types')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('label');
            $table->unique(['media_type_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_type_translations');
    }
};
