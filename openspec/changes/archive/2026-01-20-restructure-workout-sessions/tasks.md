# Tasks: Restructure Workout Sessions

## 1. Database Schema & Migration
- [x] 1.1 Create migration to add `status` enum column to `workouts` table
- [x] 1.2 Create `workout_session_exercises` table with UUID, position, notes
- [x] 1.3 Create `workout_session_exercise_sets` table with target fields (reps, weight, rpe, rest, tempo)
- [x] 1.4 Create data migration to:
  - Create default "Día 1" session for workouts with exercises
  - Move `exercise_workouts` → `workout_session_exercises`
  - Move `exercise_sets` → `workout_session_exercise_sets`
  - Convert `is_current` → `status` (true='active', false='archived')
- [x] 1.5 Drop old columns/tables (`is_current`, `exercise_workouts`, `exercise_sets`)
- [ ] 1.6 Run migrations and verify data integrity

## 2. Backend Models
- [x] 2.1 Update `Workout` model:
  - Remove `is_current` from fillable/casts
  - Add `status` to fillable with enum cast
  - Update `booted()` to enforce single active per client-trainer pair
  - Add `scopeActive()`, `scopeDraft()`, `scopeArchived()` scopes
  - Update `makeCurrentForClient()` → `activate()`
- [x] 2.2 Update `WorkoutSession` model:
  - Add `exercises()` HasMany relationship
  - Add HasUuids trait
- [x] 2.3 Create `WorkoutSessionExercise` model:
  - UUID primary key
  - Relationships: `session()`, `exercise()`, `sets()`
  - Fillable: workout_session_id, exercise_id, position, notes
- [x] 2.4 Create `WorkoutSessionExerciseSet` model:
  - UUID primary key
  - Relationships: `sessionExercise()`
  - Fillable: workout_session_exercise_id, set_order, target_reps, target_weight, target_rpe, rest_seconds, tempo
  - Casts for numeric fields
- [x] 2.5 Delete old `ExerciseWorkout` and `ExerciseSet` models

## 3. Backend Controllers & Routes
- [x] 3.1 Create `WorkoutSessionController`:
  - `store()` - add session to workout
  - `update()` - update session name/notes/order
  - `destroy()` - delete session
- [x] 3.2 Create `WorkoutSessionExerciseController`:
  - `store()` - add exercise to session
  - `update()` - update position/notes
  - `destroy()` - remove exercise from session
  - `reorder()` - batch reorder exercises
- [x] 3.3 Create `WorkoutSessionExerciseSetController`:
  - `store()` - add set to exercise
  - `update()` - update set parameters
  - `destroy()` - delete set
- [x] 3.4 Update `ClientWorkoutController`:
  - `show()` - include sessions with exercises and sets
  - Update `makeCurrent()` → `activate()`
- [x] 3.5 Update routes in `web.php`:
  - Add session routes: `workouts.sessions.*`
  - Add session exercise routes: `workout-sessions.exercises.*`
  - Add session exercise set routes: `workout-session-exercises.sets.*`
- [x] 3.6 Delete old exercise/set controllers if no longer needed
- [x] 3.7 Run `php artisan wayfinder:generate`

## 4. Frontend - Session Navigation Component
- [x] 4.1 Create `SessionNavigation.vue` component:
  - Numbered pill buttons (1, 2, 3...)
  - Active state styling for current session
  - Click handler to switch sessions
  - "+" button to add new session
- [ ] 4.2 Create `SessionSwipeContainer.vue` component:
  - Horizontal scroll/swipe container
  - Snap to session boundaries
  - Sync scroll position with active pill
  - Touch gesture support

## 5. Frontend - Workout Show Page Restructure
- [x] 5.1 Update `PageClientWorkoutShow.vue`:
  - Add SessionNavigation component at top
  - Wrap exercise list in SessionSwipeContainer
  - Show exercises for active session only
  - Update header to show status badge instead of is_current
- [ ] 5.2 Create `WorkoutSessionCard.vue` component:
  - Session name/number header
  - Exercise list within session
  - Add exercise button within session
  - Empty state for sessions with no exercises
- [x] 5.3 Update `WorkoutExercisesAddDialog.vue`:
  - Accept `sessionId` prop instead of `workoutId`
  - Update API call to session-scoped endpoint
- [x] 5.4 Update `ExerciseSetCreateDialog.vue`:
  - Update prop from `workoutExerciseId` to `sessionExerciseId`
  - Add fields for `target_rpe` and `tempo`
  - Update API call to new endpoint

## 6. Frontend - Workout List Updates
- [x] 6.1 Update `PageClientWorkoutIndex.vue`:
  - Replace `is_current` checks with `status === 'active'`
  - Show status badges (Borrador/Activa/Archivada)
  - Update "make current" action to "activate"
- [x] 6.2 Update TypeScript interfaces:
  - Add `WorkoutStatus` type
  - Update `Workout` interface
  - Add `WorkoutSession`, `WorkoutSessionExercise`, `WorkoutSessionExerciseSet` interfaces

## 7. Session Management UI
- [ ] 7.1 Create `WorkoutSessionCreateDialog.vue`:
  - Auto-assign next session_order
  - Default name "Día N" based on session_order
  - Allow trainer to customize name (optional)
- [ ] 7.2 Create `WorkoutSessionEditDialog.vue`:
  - Edit session name/notes
  - Delete session (with confirmation)
- [ ] 7.3 Add session reordering (drag handle on pills or long-press menu)

## 8. Testing & Validation
- [ ] 8.1 Write feature tests for workout session CRUD
- [ ] 8.2 Write feature tests for session exercise CRUD
- [ ] 8.3 Write feature tests for session exercise set CRUD
- [ ] 8.4 Write feature test for data migration
- [ ] 8.5 Test status enforcement (only one active per client)
- [ ] 8.6 Manual testing: create multi-day workout, navigate sessions
- [x] 8.7 Run `vendor/bin/pint --dirty` and `npm run lint`

## 9. Cleanup
- [ ] 9.1 Remove unused components/dialogs for old structure
- [x] 9.2 Remove old routes and controller methods
- [ ] 9.3 Update `openspec/project.md` with new model relationships
