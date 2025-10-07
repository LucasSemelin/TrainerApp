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
        Schema::table('exercise_workouts', function (Blueprint $table) {
            $table->dropColumn(['sets', 'min_reps', 'max_reps', 'weight']);
            $table->unsignedTinyInteger('order')->default(1)->after('exercise_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_workouts', function (Blueprint $table) {
            $table->unsignedTinyInteger('sets')->default(1)->after('exercise_id');
            $table->unsignedTinyInteger('min_reps')->default(1)->after('sets');
            $table->unsignedTinyInteger('max_reps')->nullable()->after('min_reps');
            $table->float('weight')->nullable()->after('max_reps');
            $table->dropColumn('order');
        });
    }
};
