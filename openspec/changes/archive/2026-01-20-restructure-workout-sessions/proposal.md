# Change: Restructure Workouts with Session-Based Exercise Assignment

## Why
Currently, exercises are linked directly to workouts, but trainers need to organize exercises by days/sessions within a workout routine. This restructure enables trainers to create multi-day workout plans where each session (Day 1, Day 2, etc.) has its own set of exercises with prescribed sets.

## What Changes

### Database Schema
- **BREAKING**: Rename `exercise_workouts` → `workout_session_exercises` (exercises now belong to sessions, not directly to workouts)
- **BREAKING**: Rename `exercise_sets` → `workout_session_exercise_sets`
- **BREAKING**: Replace `is_current` boolean with `status` enum (`draft|active|archived`) on workouts
- Add new fields to sets: `target_rpe`, `tempo`
- Migrate existing data: create default "Día 1" session for each workout and move its exercises there

### Backend
- Update `Workout` model: remove `is_current`, add `status` with active workout enforcement
- Update `WorkoutSession` model: add `exercises()` relationship
- Create `WorkoutSessionExercise` model (replaces `ExerciseWorkout`)
- Update `WorkoutSessionExerciseSet` model (replaces `ExerciseSet`)
- Update controllers and routes for new structure

### Frontend
- Restructure `PageClientWorkoutShow.vue` to show sessions with horizontal navigation
- Add numbered pill navigation (1, 2, 3...) with swipe gesture support
- Update exercise management to work within session context
- Update workout list to use status badges instead of is_current

## Impact
- **Affected code**:
  - Models: `Workout`, `WorkoutSession`, `ExerciseWorkout` → `WorkoutSessionExercise`, `ExerciseSet` → `WorkoutSessionExerciseSet`
  - Controllers: `WorkoutController`, `ClientWorkoutController`, `ExerciseSetController`
  - Pages: `PageClientWorkoutShow.vue`, `PageClientWorkoutIndex.vue`
  - Components: `WorkoutExercisesAddDialog.vue`, `ExerciseSetCreateDialog.vue`
- **Migration required**: Data migration to move exercises from workout-level to session-level
- **API breaking changes**: Exercise endpoints will change to be session-scoped
