<script setup lang="ts">
import ExerciseSetCreateDialog from '@/components/ExerciseSetCreateDialog.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import WorkoutExercisesAddDialog from '@/components/WorkoutExercisesAddDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, usePage } from '@inertiajs/vue3';
import { PencilLine, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Exercise {
    id: string;
    name: string;
}

interface Set {
    id: string;
    workout_exercise_id: string;
    set_number: number;
    min_reps: number;
    max_reps: number;
    weight: number;
    rest_time_seconds: number;
    notes?: string;
}

interface WorkoutExercise {
    id: string;
    workout_id: string;
    exercise_id: string;
    exercise: Exercise;
    sets: Set[];
}

interface Workout {
    id: string;
    name: string;
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
const showSetDialog = ref(false);
const showDeleteConfirmDialog = ref(false);
const showEditNotesDialog = ref(false);
const selectedExerciseId = ref<string>('');
const setToDelete = ref<{ setId: string; exerciseId: string } | null>(null);
const setToEdit = ref<{ setId: string; exerciseId: string; currentNotes: string } | null>(null);
const notesText = ref<string>('');

// Get existing sets for selected exercise
const selectedExerciseSets = computed(() => {
    const exercise = exercises.value.find((ex) => ex.id === selectedExerciseId.value);
    return exercise?.sets || [];
});

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

// Handle opening set dialog
const openSetDialog = (exerciseId: string) => {
    selectedExerciseId.value = exerciseId;
    showSetDialog.value = true;
};

// Handle edit notes for a set
const editSetNotes = (setId: string, exerciseId: string) => {
    // Find the set to get current notes
    const exercise = exercises.value.find((ex) => ex.id === exerciseId);
    const set = exercise?.sets?.find((s) => s.id === setId);

    setToEdit.value = {
        setId,
        exerciseId,
        currentNotes: set?.notes || '',
    };
    notesText.value = set?.notes || '';
    showEditNotesDialog.value = true;
};

// Save edited notes
const saveEditedNotes = async () => {
    if (!setToEdit.value) return;

    const { setId, exerciseId } = setToEdit.value;

    try {
        const response = await fetch(`/exercise-sets/${setId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                notes: notesText.value,
            }),
        });

        if (response.ok) {
            // Update the set in the local state
            const exercise = exercises.value.find((ex) => ex.id === exerciseId);
            const set = exercise?.sets?.find((s) => s.id === setId);
            if (set) {
                set.notes = notesText.value;
            }
        } else {
            alert('Error al guardar las notas');
        }
    } catch (error) {
        console.error('Error al guardar las notas:', error);
        alert('Error al guardar las notas');
    } finally {
        showEditNotesDialog.value = false;
        setToEdit.value = null;
        notesText.value = '';
    }
};

// Handle set created
const onSetCreated = (newSet: Set) => {
    const exercise = exercises.value.find((ex) => ex.id === selectedExerciseId.value);
    if (exercise) {
        if (!exercise.sets) {
            exercise.sets = [];
        }
        exercise.sets.push(newSet);
        // Sort sets by set_number
        exercise.sets.sort((a, b) => a.set_number - b.set_number);
    }
};

// Handle set deletion - Show confirmation dialog
const deleteSet = (setId: string, exerciseId: string) => {
    setToDelete.value = { setId, exerciseId };
    showDeleteConfirmDialog.value = true;
};

// Confirm set deletion
const confirmDeleteSet = async () => {
    if (!setToDelete.value) return;

    const { setId, exerciseId } = setToDelete.value;

    try {
        const response = await fetch(`/exercise-sets/${setId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            // Remove set from the exercise
            const exercise = exercises.value.find((ex) => ex.id === exerciseId);
            if (exercise && exercise.sets) {
                exercise.sets = exercise.sets.filter((set) => set.id !== setId);
            }
        } else {
            alert('Error al eliminar la serie');
        }
    } catch (error) {
        console.error('Error al eliminar la serie:', error);
        alert('Error al eliminar la serie');
    } finally {
        showDeleteConfirmDialog.value = false;
        setToDelete.value = null;
    }
};
</script>

<template>
    <Head title="Detalle de Rutina" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 pb-24">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ workout.name }}</h1>
                    <p class="text-muted-foreground">
                        {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }} • Creada el
                        {{ formatDate(workout.created_at) }}
                    </p>
                </div>
            </div>

            <!-- Exercises List -->
            <div v-if="exercises.length > 0">
                <div class="space-y-4">
                    <div v-for="exercise in exercises" :key="exercise.id" class="border-b border-border/70 pb-4 dark:border-border">
                        <!-- Exercise Name - Full width -->
                        <div class="mb-3">
                            <h3 class="text-lg font-medium">{{ exercise.exercise.name }}</h3>
                        </div>

                        <!-- Sets List -->
                        <div v-if="exercise.sets && exercise.sets.length > 0" class="mb-3">
                            <!-- Sets Header - Hidden on mobile -->
                            <div class="mb-2 hidden grid-cols-5 gap-4 px-3 text-xs font-medium text-muted-foreground sm:grid">
                                <div>Repeticiones</div>
                                <div>Peso</div>
                                <div>Descanso</div>
                                <div>Notas</div>
                                <div class="text-right">Acciones</div>
                            </div>

                            <!-- Sets Data -->
                            <div class="space-y-1">
                                <div v-for="set in exercise.sets" :key="set.id" class="rounded-md bg-muted/50 p-3 text-sm">
                                    <!-- Mobile layout -->
                                    <div class="grid grid-cols-5 gap-3 sm:hidden">
                                        <div class="col-span-4 space-y-1">
                                            <div class="font-medium">
                                                {{ set.min_reps
                                                }}{{ set.max_reps && set.max_reps !== set.min_reps ? `-${set.max_reps}` : '' }} repeticiones
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ set.weight ? `${set.weight} kg` : 'Sin peso' }} •
                                                {{ set.rest_time_seconds ? `${set.rest_time_seconds}s descanso` : 'Sin descanso' }}
                                            </div>
                                            <div v-if="set.notes" class="text-xs break-words text-muted-foreground">
                                                {{ set.notes }}
                                            </div>
                                        </div>
                                        <div class="col-span-1 flex items-start justify-end gap-1">
                                            <button
                                                type="button"
                                                @click="editSetNotes(set.id, exercise.id)"
                                                class="inline-flex items-center justify-center rounded-md p-1.5 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                                aria-label="Editar notas"
                                            >
                                                <PencilLine class="h-3.5 w-3.5" />
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteSet(set.id, exercise.id)"
                                                class="inline-flex items-center justify-center rounded-md p-1.5 text-sm font-medium ring-offset-background transition-colors hover:bg-destructive/10 hover:text-destructive focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                                aria-label="Eliminar serie"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Desktop layout -->
                                    <div class="hidden grid-cols-5 items-center gap-4 sm:grid">
                                        <div class="font-medium">
                                            {{ set.min_reps }}{{ set.max_reps && set.max_reps !== set.min_reps ? `-${set.max_reps}` : '' }} reps
                                        </div>
                                        <div class="text-muted-foreground">
                                            {{ set.weight ? `${set.weight} kg` : 'Sin peso' }}
                                        </div>
                                        <div class="text-muted-foreground">
                                            {{ set.rest_time_seconds ? `${set.rest_time_seconds}s` : 'Sin descanso' }}
                                        </div>
                                        <div class="line-clamp-3 max-w-[200px] break-words text-muted-foreground" :title="set.notes || ''">
                                            {{ set.notes || '-' }}
                                        </div>
                                        <div class="flex justify-end gap-1">
                                            <button
                                                type="button"
                                                @click="editSetNotes(set.id, exercise.id)"
                                                class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                                aria-label="Editar notas"
                                            >
                                                <PencilLine class="h-4 w-4" />
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteSet(set.id, exercise.id)"
                                                class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-destructive/10 hover:text-destructive focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                                aria-label="Eliminar serie"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No sets message -->
                        <div v-else class="mb-3 text-sm text-muted-foreground">No hay series configuradas para este ejercicio.</div>

                        <!-- Add Set Button -->
                        <button
                            type="button"
                            @click="openSetDialog(exercise.id)"
                            class="w-full rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            <Plus class="mr-2 inline h-4 w-4" />
                            Agregar Serie
                        </button>
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

        <!-- Add Set Dialog -->
        <ExerciseSetCreateDialog
            v-model:open="showSetDialog"
            :workout-exercise-id="selectedExerciseId"
            :existing-sets="selectedExerciseSets"
            @created="onSetCreated"
        />

        <!-- Edit Notes Dialog -->
        <Dialog v-model:open="showEditNotesDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Editar Notas</DialogTitle>
                    <DialogDescription> Agrega o modifica las notas para esta serie. </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="notes">Notas</Label>
                        <textarea
                            id="notes"
                            v-model="notesText"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Escribe las notas para esta serie..."
                            rows="4"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showEditNotesDialog = false"> Cancelar </Button>
                    <Button @click="saveEditedNotes"> Guardar notas </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteConfirmDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Eliminar Serie</DialogTitle>
                    <DialogDescription> ¿Estás seguro de que querés eliminar esta serie? </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteConfirmDialog = false"> Cancelar </Button>
                    <Button variant="destructive" @click="confirmDeleteSet"> Eliminar serie </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
