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
        Schema::table('exercises', function (Blueprint $table) {
            $table->foreignId('difficulty_id')->nullable()->constrained()->onDelete('cascade')->after('metadata');
            $table->foreignId('movement_pattern_id')->nullable()->constrained()->onDelete('cascade')->after('difficulty_id');
            $table->foreignId('mechanic_id')->nullable()->constrained()->onDelete('cascade')->after('movement_pattern_id');
            $table->foreignId('force_type_id')->nullable()->constrained()->onDelete('cascade')->after('mechanic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropForeign(['difficulty_id']);
            $table->dropForeign(['movement_pattern_id']);
            $table->dropForeign(['mechanic_id']);
            $table->dropForeign(['force_type_id']);
            $table->dropColumn(['difficulty_id', 'movement_pattern_id', 'mechanic_id', 'force_type_id']);
        });
    }
};
