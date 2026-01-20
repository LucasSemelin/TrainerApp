export type WorkoutStatus = 'draft' | 'active' | 'archived';

export interface Exercise {
    id: string;
    name: string;
    categories?: string[];
}

export interface WorkoutSessionExerciseSet {
    id: string;
    workout_session_exercise_id: string;
    set_order: number;
    target_reps: number | null;
    target_weight: number | null;
    target_rpe: number | null;
    rest_seconds: number | null;
    tempo: string | null;
    created_at: string;
    updated_at: string;
}

export interface WorkoutSessionExercise {
    id: string;
    workout_session_id: string;
    exercise_id: string;
    position: number;
    notes: string | null;
    exercise: Exercise;
    sets: WorkoutSessionExerciseSet[];
    created_at: string;
    updated_at: string;
}

export interface WorkoutSession {
    id: string;
    workout_id: string;
    session_order: number;
    name: string | null;
    notes: string | null;
    exercises: WorkoutSessionExercise[];
    created_at: string;
    updated_at: string;
}

export interface Workout {
    id: string;
    name: string;
    trainer_id: string | null;
    client_id: string;
    status: WorkoutStatus;
    sessions?: WorkoutSession[];
    created_at: string;
    updated_at: string;
}
