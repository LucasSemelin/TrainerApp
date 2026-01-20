# Design: Workout Session Restructure

## Context
TrainerApp needs to support multi-day workout routines where trainers can assign different exercises to different days/sessions. The current schema links exercises directly to workouts, which doesn't support this use case.

### Stakeholders
- **Trainers**: Create and manage multi-day workout routines for clients
- **Clients**: View their assigned workout by day/session (future: execute training)

### Constraints
- Must migrate existing workout data without loss
- Mobile-first UI with touch-friendly navigation
- Maintain performance with eager loading patterns

## Goals / Non-Goals

### Goals
- Enable trainers to create multi-day workout routines
- Provide intuitive session navigation (pills + swipe)
- Support prescribing sets with reps, weight, RPE, tempo, rest
- Enforce single active workout per client via status field

### Non-Goals
- Training execution/logging (future phase - `training_sessions` tables)
- Workout templates/copying
- Superset/circuit exercise grouping

## Decisions

### 1. Session-Based Exercise Assignment
**Decision**: Exercises belong to sessions, not directly to workouts.

**Rationale**:
- Matches real-world workout structure (Day 1: Push, Day 2: Pull, etc.)
- Enables reordering exercises within specific days
- Cleaner data model for future training tracking

**Alternatives considered**:
- Keep workout-level exercises with "day" tag → Rejected: doesn't enforce structure
- Flat exercise list with day number field → Rejected: harder to manage ordering

### 2. Status Enum vs Boolean
**Decision**: Replace `is_current` with `status` enum (`draft|active|archived`).

**Rationale**:
- More expressive states (draft for incomplete routines)
- Clearer business logic (status transitions)
- Only one `active` workout per client-trainer pair enforced at application level
- A client with multiple trainers can have one active workout per trainer

**Migration**:
- `is_current = true` → `status = 'active'`
- `is_current = false` → `status = 'archived'`

### 3. Table Naming Convention
**Decision**: Use `workout_session_exercises` and `workout_session_exercise_sets`.

**Rationale**:
- Follows Laravel pivot table conventions
- Clear hierarchy: workout → session → exercise → set
- Consistent with existing naming patterns

### 4. Frontend Navigation Pattern
**Decision**: Numbered pills (1, 2, 3...) + horizontal swipe.

**Rationale**:
- Pills provide clear visual affordance for session count
- Numbers are language-agnostic
- Swipe gesture is natural on mobile
- Combination ensures discoverability for users who don't try swiping

## Database Schema

```
workouts
├── id (uuid, pk)
├── client_id (uuid, fk → users)
├── coach_id (uuid, fk → users, nullable)
├── name (varchar)
├── status (enum: draft|active|archived)
├── created_at, updated_at

workout_sessions
├── id (uuid, pk)
├── workout_id (uuid, fk → workouts)
├── session_order (int)
├── name (varchar, nullable) -- e.g., "Push Day"
├── notes (text, nullable)
├── created_at, updated_at
├── UNIQUE (workout_id, session_order)

workout_session_exercises
├── id (uuid, pk)
├── workout_session_id (uuid, fk → workout_sessions)
├── exercise_id (uuid, fk → exercises)
├── position (int)
├── notes (text, nullable)
├── created_at, updated_at
├── UNIQUE (workout_session_id, position)

workout_session_exercise_sets
├── id (uuid, pk)
├── workout_session_exercise_id (uuid, fk → workout_session_exercises)
├── set_order (int)
├── target_reps (int, nullable)
├── target_weight (decimal, nullable)
├── target_rpe (decimal, nullable)
├── rest_seconds (int, nullable)
├── tempo (varchar, nullable) -- e.g., "3-1-2-0"
├── created_at, updated_at
├── UNIQUE (workout_session_exercise_id, set_order)
```

## Migration Plan

### Phase 1: Schema Changes
1. Add `status` column to `workouts` (default: 'draft')
2. Create `workout_session_exercises` table
3. Create `workout_session_exercise_sets` table
4. Migrate data:
   - For each workout with exercises, create "Día 1" session
   - Move `exercise_workouts` records to `workout_session_exercises`
   - Move `exercise_sets` records to `workout_session_exercise_sets`
   - Set `status` based on `is_current`
5. Drop old tables and `is_current` column

### Phase 2: Backend Updates
1. Update models and relationships
2. Update/create controllers for new structure
3. Update routes

### Phase 3: Frontend Updates
1. Add session navigation component (pills + swipe)
2. Update workout show page layout
3. Update exercise/set management dialogs
4. Update workout list to show status badges

### Rollback Strategy
- Keep backup of old tables during migration window
- Feature flag for new UI (optional, may not be needed for single-tenant)

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| Data loss during migration | Backup old tables, test on staging first |
| Breaking API changes | Update all affected controllers atomically |
| Mobile swipe conflicts | Test with iOS/Android gesture navigation |

## Open Questions
- ~~Should we support workout templates?~~ → Out of scope (future)
- ~~Superset/circuit grouping?~~ → Out of scope (future)
