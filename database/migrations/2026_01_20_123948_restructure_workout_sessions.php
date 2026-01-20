<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration restructures the workout system to support session-based exercise assignment:
     * 1. Adds status enum to workouts (replacing is_current boolean)
     * 2. Converts workout_sessions to use UUID
     * 3. Creates workout_session_exercises table (replaces exercise_workouts)
     * 4. Creates workout_session_exercise_sets table (replaces exercise_sets)
     * 5. Migrates existing data
     * 6. Drops old tables and columns
     */
    public function up(): void
    {
        // Step 1: Add status column to workouts
        // Schema::table('workouts', function (Blueprint $table) {
        //     $table->string('status', 20)->default('draft')->after('client_id');
        //     $table->index(['client_id', 'trainer_id', 'status']);
        // });

        // // Step 2: Convert is_current to status (only if column exists)
        // if (Schema::hasColumn('workouts', 'is_current')) {
        //     DB::table('workouts')->where('is_current', true)->update(['status' => 'active']);
        //     DB::table('workouts')->where('is_current', false)->update(['status' => 'archived']);
        // }

        // Step 3: Drop old workout_sessions table and recreate with UUID
        // First drop tables that have foreign keys to workout_sessions
        Schema::dropIfExists('workout_session_exercise_sets');
        Schema::dropIfExists('workout_session_exercises');
        Schema::dropIfExists('workout_sessions');

        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workout_id')->constrained('workouts')->cascadeOnDelete();
            $table->unsignedInteger('session_order')->default(1);
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workout_id', 'session_order']);
        });

        // Step 4: Create workout_session_exercises table
        Schema::create('workout_session_exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workout_session_id')->constrained('workout_sessions')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workout_session_id', 'position']);
        });

        // Step 5: Create workout_session_exercise_sets table
        Schema::create('workout_session_exercise_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workout_session_exercise_id');
            $table->foreign('workout_session_exercise_id', 'wse_sets_wse_id_fk')
                ->references('id')
                ->on('workout_session_exercises')
                ->cascadeOnDelete();
            $table->unsignedInteger('set_order')->default(1);
            $table->unsignedInteger('target_reps')->nullable();
            $table->decimal('target_weight', 8, 2)->nullable();
            $table->decimal('target_rpe', 3, 1)->nullable();
            $table->unsignedInteger('rest_seconds')->nullable();
            $table->string('tempo', 20)->nullable();
            $table->timestamps();

            $table->unique(['workout_session_exercise_id', 'set_order'], 'wse_sets_unique');
        });

        // Step 6: Migrate data from exercise_workouts to workout_session_exercises (only if tables exist)
        if (Schema::hasTable('exercise_workouts') && Schema::hasTable('exercise_sets')) {
            $workoutsWithExercises = DB::table('exercise_workouts')
                ->select('workout_id')
                ->distinct()
                ->get();

            foreach ($workoutsWithExercises as $workoutRow) {
                // Create default session "Día 1" for each workout
                $sessionId = Str::uuid()->toString();
                DB::table('workout_sessions')->insert([
                    'id' => $sessionId,
                    'workout_id' => $workoutRow->workout_id,
                    'session_order' => 1,
                    'name' => 'Día 1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Move exercises to the new session
                $exerciseWorkouts = DB::table('exercise_workouts')
                    ->where('workout_id', $workoutRow->workout_id)
                    ->orderBy('order')
                    ->get();

                foreach ($exerciseWorkouts as $exerciseWorkout) {
                    $sessionExerciseId = Str::uuid()->toString();
                    DB::table('workout_session_exercises')->insert([
                        'id' => $sessionExerciseId,
                        'workout_session_id' => $sessionId,
                        'exercise_id' => $exerciseWorkout->exercise_id,
                        'position' => $exerciseWorkout->order,
                        'notes' => null,
                        'created_at' => $exerciseWorkout->created_at,
                        'updated_at' => $exerciseWorkout->updated_at,
                    ]);

                    // Move sets to the new structure
                    $sets = DB::table('exercise_sets')
                        ->where('exercise_workout_id', $exerciseWorkout->id)
                        ->orderBy('set_number')
                        ->get();

                    foreach ($sets as $set) {
                        DB::table('workout_session_exercise_sets')->insert([
                            'id' => Str::uuid()->toString(),
                            'workout_session_exercise_id' => $sessionExerciseId,
                            'set_order' => $set->set_number,
                            'target_reps' => $set->min_reps ?? $set->max_reps,
                            'target_weight' => $set->weight,
                            'target_rpe' => null,
                            'rest_seconds' => $set->rest_time_seconds,
                            'tempo' => null,
                            'created_at' => $set->created_at,
                            'updated_at' => $set->updated_at,
                        ]);
                    }
                }
            }
        }

        // Step 7: Also create a default session for workouts without exercises
        $workoutsWithoutSessions = DB::table('workouts')
            ->whereNotIn('id', function ($query) {
                $query->select('workout_id')->from('workout_sessions');
            })
            ->get();

        foreach ($workoutsWithoutSessions as $workout) {
            DB::table('workout_sessions')->insert([
                'id' => Str::uuid()->toString(),
                'workout_id' => $workout->id,
                'session_order' => 1,
                'name' => 'Día 1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Step 8: Drop old tables and columns
        Schema::dropIfExists('exercise_sets');
        Schema::dropIfExists('exercise_workouts');

        if (Schema::hasColumn('workouts', 'is_current')) {
            Schema::table('workouts', function (Blueprint $table) {
                $table->dropColumn('is_current');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate old tables
        Schema::create('exercise_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('workout_id')->constrained('workouts')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->timestamps();
        });

        Schema::create('exercise_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_workout_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('set_number');
            $table->float('weight')->nullable();
            $table->unsignedTinyInteger('min_reps')->nullable();
            $table->unsignedTinyInteger('max_reps')->nullable();
            $table->unsignedInteger('rest_time_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add back is_current column
        Schema::table('workouts', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('client_id');
            $table->index(['client_id', 'is_current']);
        });

        // Migrate data back (simplified - loses some data)
        $sessions = DB::table('workout_sessions')->get();
        foreach ($sessions as $session) {
            $exercises = DB::table('workout_session_exercises')
                ->where('workout_session_id', $session->id)
                ->orderBy('position')
                ->get();

            foreach ($exercises as $exercise) {
                $exerciseWorkoutId = DB::table('exercise_workouts')->insertGetId([
                    'workout_id' => $session->workout_id,
                    'exercise_id' => $exercise->exercise_id,
                    'order' => $exercise->position,
                    'created_at' => $exercise->created_at,
                    'updated_at' => $exercise->updated_at,
                ]);

                $sets = DB::table('workout_session_exercise_sets')
                    ->where('workout_session_exercise_id', $exercise->id)
                    ->orderBy('set_order')
                    ->get();

                foreach ($sets as $set) {
                    DB::table('exercise_sets')->insert([
                        'exercise_workout_id' => $exerciseWorkoutId,
                        'set_number' => $set->set_order,
                        'weight' => $set->target_weight,
                        'min_reps' => $set->target_reps,
                        'max_reps' => $set->target_reps,
                        'rest_time_seconds' => $set->rest_seconds,
                        'notes' => null,
                        'created_at' => $set->created_at,
                        'updated_at' => $set->updated_at,
                    ]);
                }
            }
        }

        // Convert status back to is_current
        DB::table('workouts')->where('status', 'active')->update(['is_current' => true]);
        DB::table('workouts')->whereIn('status', ['draft', 'archived'])->update(['is_current' => false]);

        // Drop new tables
        Schema::dropIfExists('workout_session_exercise_sets');
        Schema::dropIfExists('workout_session_exercises');
        Schema::dropIfExists('workout_sessions');

        // Recreate old workout_sessions table
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('workout_id')->constrained('workouts')->onDelete('cascade');
            $table->integer('session_order')->default(1);
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workout_id', 'session_order']);
        });

        // Drop status column
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'trainer_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
