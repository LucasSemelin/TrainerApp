PLAN (lo que el coach diseña)
workouts

id (uuid, pk)

client_id (uuid, fk → users/clients)

coach_id (uuid, fk → users/coaches)

name (text)

status (text: draft|active|archived)

created_at, updated_at

workout_sessions (plantilla: Sesión 1..N / Día 1..N)

id (uuid, pk)

workout_id (uuid, fk → workouts.id)

session_order (int) (1..N)

name (text, null)

notes (text, null)

created_at, updated_at

Constraint:

UNIQUE (workout_id, session_order)

workout_session_exercises

id (uuid, pk)

workout_session_id (uuid, fk → workout_sessions.id)

exercise_id (uuid, fk → exercises.id)

position (int) (orden dentro de la sesión)

notes (text, null)

created_at, updated_at

Constraint:

UNIQUE (workout_session_id, position)

workout_session_exercise_sets (prescripción por set)

id (uuid, pk)

workout_session_exercise_id (uuid, fk → workout_session_exercises.id)

set_order (int) (1..N)

target_reps (int, null)

target_weight (numeric, null)

target_rpe (numeric, null)

rest_seconds (int, null)

tempo (text, null)

created_at, updated_at

Constraint:

UNIQUE (workout_session_exercise_id, set_order)

ASIGNACIÓN / VERSIONADO
workout_assignments

id (uuid, pk)

client_id (uuid, fk → users/clients)

coach_id (uuid, fk → users/coaches)

workout_id (uuid, fk → workouts.id) (la “versión” asignada)

assigned_at (timestamptz)

ended_at (timestamptz, null)

is_active (bool) (opcional si usás ended_at)

created_at, updated_at

Regla:

Solo 1 activo por client: UNIQUE parcial por (client_id) WHERE ended_at IS NULL

EJECUCIÓN / HISTORIAL (lo que realmente hizo el cliente)
training_sessions

id (uuid, pk)

client_id (uuid, fk → users/clients)

coach_id (uuid, fk → users/coaches, null) (opcional)

workout_assignment_id (uuid, fk → workout_assignments.id)

workout_id (uuid, fk → workouts.id) (redundante opcional, para queries rápidas)

workout_session_id (uuid, fk → workout_sessions.id) (qué “día/sesión” ejecutó)

performed_at (timestamptz)

duration_seconds (int, null)

notes (text, null)

created_at, updated_at

training_session_exercises

id (uuid, pk)

training_session_id (uuid, fk → training_sessions.id)

exercise_id (uuid, fk → exercises.id)

workout_session_exercise_id (uuid, fk → workout_session_exercises.id, null) (link al plan)

position (int)

notes (text, null)

created_at, updated_at

Constraint:

UNIQUE (training_session_id, position)

training_session_sets

id (uuid, pk)

training_session_exercise_id (uuid, fk → training_session_exercises.id)

set_order (int)

reps (int, null)

weight (numeric, null)

rpe (numeric, null)

rest_seconds (int, null)

is_warmup (bool, default false)

created_at, updated_at

Constraint:

UNIQUE (training_session_exercise_id, set_order)
