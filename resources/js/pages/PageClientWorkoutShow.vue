<script setup lang="ts">
import WorkoutExercisesAddDialog from '@/components/WorkoutExercisesAddDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, usePage } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Exercise {
    id: string;
    name: string;
}

interface WorkoutExercise {
    id: string;
    workout_id: string;
    exercise_id: string;
    sets: number;
    min_reps: number;
    max_reps: number;
    weight: number;
    exercise: Exercise;
}

interface Workout {
    id: string;
    trainer_id: string | null;
    client_id: string;
    created_at: string;
    updated_at: string;
    exercises: WorkoutExercise[];
}

const page = usePage();
const client = computed<Client>(() => page.props.client as Client);
const workout = computed<Workout>(() => page.props.workout as Workout);

// Use reactive ref for exercises to allow dynamic updates
const exercises = ref<WorkoutExercise[]>(page.props.exercises as WorkoutExercise[]);

// Dialog state
const showAddDialog = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Alumnos',
        href: clients.index().url,
    },
    {
        title: 'Rutinas',
        href: clients.workouts.index({ client: client.value.id }).url,
    },
    {
        title: 'Detalle de Rutina',
        href: '#',
    },
];

// Format date to DD/MM/YYYY
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

// Handle exercise added
const onExerciseAdded = (newExercise: WorkoutExercise) => {
    exercises.value.push(newExercise);
};
</script>

<template>
    <Head title="Detalle de Rutina" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 pb-24">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Rutina del {{ formatDate(workout.created_at) }}</h1>
                    <p class="text-muted-foreground">
                        {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                    </p>
                </div>
            </div>

            <!-- Exercises List -->
            <div v-if="exercises.length > 0">
                <div class="space-y-4">
                    <div v-for="exercise in exercises" :key="exercise.id" class="border-b border-border/70 pb-4 dark:border-border">
                        <!-- Exercise Name - Full width -->
                        <div class="mb-2">
                            <h3 class="text-lg font-medium">{{ exercise.exercise.name }}</h3>
                        </div>

                        <!-- Exercise Details -->
                        <div class="flex justify-between text-sm text-muted-foreground">
                            <span>Series: {{ exercise.sets }}</span>
                            <span
                                >Reps: {{ exercise.min_reps
                                }}{{ exercise.max_reps && exercise.max_reps !== exercise.min_reps ? `-${exercise.max_reps}` : '' }}</span
                            >
                            <span>{{ exercise.weight ? `Peso: ${exercise.weight} kg` : 'Sin Peso' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Add Exercise Button -->
                <div class="mt-6">
                    <button
                        type="button"
                        @click="showAddDialog = true"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        <Plus class="h-4 w-4" />
                        Agregar Ejercicio
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                <p class="text-muted-foreground">No hay ejercicios en esta rutina.</p>

                <!-- Add Exercise Button for Empty State -->
                <div class="mt-4">
                    <button
                        type="button"
                        @click="showAddDialog = true"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        <Plus class="h-4 w-4" />
                        Agregar Primer Ejercicio
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Exercise Dialog -->
        <WorkoutExercisesAddDialog v-model:open="showAddDialog" :workout-id="workout.id" @created="onExerciseAdded" />
    </AppLayout>
</template>
