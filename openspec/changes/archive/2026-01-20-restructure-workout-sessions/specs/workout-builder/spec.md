# Workout Builder Specification

## ADDED Requirements

### Requirement: Workout Status Management
The system SHALL support workout status management with three states: draft, active, and archived.

#### Scenario: Create workout with draft status
- **WHEN** a trainer creates a new workout for a client
- **THEN** the workout status SHALL be set to 'draft' by default

#### Scenario: Activate a workout
- **WHEN** a trainer activates a draft or archived workout
- **THEN** that workout status SHALL change to 'active'
- **AND** any previously active workout for the same client-trainer pair SHALL change to 'archived'

#### Scenario: Archive a workout
- **WHEN** a trainer archives an active workout
- **THEN** the workout status SHALL change to 'archived'

#### Scenario: Only one active workout per client-trainer pair
- **WHEN** a client has multiple workouts with the same trainer
- **THEN** at most one workout SHALL have status 'active' for that trainer
- **AND** the client MAY have active workouts with other trainers simultaneously

---

### Requirement: Workout Session Management
The system SHALL support multiple sessions (days) within a workout, where each session contains its own set of exercises.

#### Scenario: Workout has at least one session
- **WHEN** a workout is created
- **THEN** it SHALL have at least one session with default name "Día 1"

#### Scenario: Add session to workout
- **WHEN** a trainer adds a new session to a workout
- **THEN** the session SHALL be assigned the next sequential order number
- **AND** the session name SHALL default to "Día N" where N is the session order (e.g., "Día 2", "Día 3")
- **AND** the trainer MAY change the session name later

#### Scenario: Reorder sessions
- **WHEN** a trainer reorders sessions within a workout
- **THEN** the session_order values SHALL be updated to reflect the new order
- **AND** the unique constraint (workout_id, session_order) SHALL be maintained

#### Scenario: Delete session
- **WHEN** a trainer deletes a session from a workout
- **THEN** all exercises and sets within that session SHALL be deleted
- **AND** remaining sessions SHALL maintain their order

---

### Requirement: Session Exercise Management
The system SHALL support adding, removing, and reordering exercises within a workout session.

#### Scenario: Add exercise to session
- **WHEN** a trainer adds an exercise to a session
- **THEN** the exercise SHALL be assigned the next sequential position
- **AND** the exercise SHALL be linked to the session (not the workout directly)

#### Scenario: Reorder exercises within session
- **WHEN** a trainer reorders exercises within a session
- **THEN** the position values SHALL be updated to reflect the new order
- **AND** the unique constraint (workout_session_id, position) SHALL be maintained

#### Scenario: Remove exercise from session
- **WHEN** a trainer removes an exercise from a session
- **THEN** all sets for that exercise within the session SHALL be deleted
- **AND** remaining exercises SHALL maintain their positions

#### Scenario: Add notes to session exercise
- **WHEN** a trainer adds notes to a session exercise
- **THEN** the notes SHALL be saved and displayed with the exercise

---

### Requirement: Exercise Set Prescription
The system SHALL support prescribing sets for each exercise in a session with target parameters.

#### Scenario: Add set to session exercise
- **WHEN** a trainer adds a set to an exercise
- **THEN** the set SHALL be assigned the next sequential set_order
- **AND** the trainer MAY specify: target_reps, target_weight, target_rpe, rest_seconds, tempo

#### Scenario: Update set parameters
- **WHEN** a trainer updates set parameters
- **THEN** the changes SHALL be saved
- **AND** any field MAY be null (optional)

#### Scenario: Delete set
- **WHEN** a trainer deletes a set
- **THEN** the set SHALL be removed
- **AND** remaining sets SHALL maintain their order

#### Scenario: Tempo format
- **WHEN** a trainer specifies tempo
- **THEN** it SHALL be stored as a string (e.g., "3-1-2-0" for eccentric-pause-concentric-pause)

---

### Requirement: Session Navigation UI
The system SHALL provide an intuitive interface for navigating between workout sessions on mobile devices.

#### Scenario: Display session pills
- **WHEN** viewing a workout with multiple sessions
- **THEN** numbered pill buttons (1, 2, 3...) SHALL be displayed
- **AND** the active session pill SHALL be visually highlighted

#### Scenario: Navigate via pill tap
- **WHEN** a user taps a session pill
- **THEN** the view SHALL switch to that session's exercises

#### Scenario: Navigate via horizontal swipe
- **WHEN** a user swipes horizontally on the exercise area
- **THEN** the view SHALL switch to the adjacent session
- **AND** the active pill indicator SHALL update accordingly

#### Scenario: Add session button
- **WHEN** viewing session navigation
- **THEN** a "+" button SHALL be available to add a new session

---

### Requirement: Workout List Status Display
The system SHALL display workout status clearly in the workout list view.

#### Scenario: Display active workout
- **WHEN** viewing the workout list for a client
- **THEN** the active workout (if any) SHALL be displayed prominently at the top
- **AND** SHALL show an "Activa" badge

#### Scenario: Display draft workouts
- **WHEN** a workout has status 'draft'
- **THEN** it SHALL show a "Borrador" badge

#### Scenario: Display archived workouts
- **WHEN** a workout has status 'archived'
- **THEN** it SHALL be shown in an "Anteriores" section
- **AND** SHALL have an option to reactivate

---

## MODIFIED Requirements

*(None - this is a new capability)*

## REMOVED Requirements

*(None)*
