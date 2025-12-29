<script setup lang="ts">
import ExerciseSetCreateDialog from '@/components/ExerciseSetCreateDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import WorkoutExercisesAddDialog from '@/components/WorkoutExercisesAddDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, usePage } from '@inertiajs/vue3';
import { Dumbbell, MoreVertical, PencilLine, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Exercise {
    id: string;
    name: string;
    categories?: string[];
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
    is_current: boolean;
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
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 pb-24">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-semibold">{{ workout.name }}</h1>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md p-1 text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <PencilLine class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="mt-1 flex items-center gap-2">
                        <Badge v-if="workout.is_current" variant="default" class="bg-primary/20 text-primary hover:bg-primary/30"> Actual </Badge>
                        <span class="text-sm text-muted-foreground">
                            {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Exercises List -->
            <div v-if="exercises.length > 0" class="space-y-4">
                <!-- Exercise Card -->
                <div v-for="exercise in exercises" :key="exercise.id" class="overflow-hidden rounded-2xl border border-border bg-card">
                    <!-- Exercise Header -->
                    <div class="flex items-start gap-4 p-4">
                        <!-- Exercise Image Placeholder -->
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                            <Dumbbell class="h-10 w-10 text-primary" />
                        </div>

                        <!-- Exercise Info -->
                        <div class="flex min-w-0 flex-1 flex-col">
                            <h3 class="text-lg font-semibold">{{ exercise.exercise.name }}</h3>
                            <div v-if="exercise.exercise.categories && exercise.exercise.categories.length > 0" class="mt-1">
                                <span class="text-sm text-muted-foreground">
                                    {{ exercise.exercise.categories.join(' • ') }}
                                </span>
                            </div>
                        </div>

                        <!-- Menu Button -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                >
                                    <MoreVertical class="h-5 w-5" />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem> Detalles del ejercicio </DropdownMenuItem>
                                <DropdownMenuItem @click="openDeleteDialog(exercise.id)" class="text-destructive focus:text-destructive">
                                    Eliminar
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Sets Info -->
                    <div v-if="exercise.sets && exercise.sets.length > 0" class="border-t border-border/50 px-4 py-3">
                        <!-- Sets Summary -->
                        <div class="text-sm text-muted-foreground">
                            {{ exercise.sets.length }} series • {{ exercise.sets[0]?.min_reps
                            }}{{
                                exercise.sets[0]?.max_reps && exercise.sets[0]?.max_reps !== exercise.sets[0]?.min_reps
                                    ? `-${exercise.sets[0]?.max_reps}`
                                    : ''
                            }}
                            Reps
                        </div>
                    </div>

                    <!-- No sets message -->
                    <div v-else class="border-t border-border/50 px-4 py-3">
                        <p class="text-sm text-muted-foreground">No hay series configuradas</p>
                    </div>
                </div>

                <!-- Add Exercise Button -->
                <button
                    type="button"
                    @click="showAddDialog = true"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-border px-6 py-4 text-sm font-medium text-muted-foreground transition-colors hover:border-primary hover:bg-primary/5 hover:text-primary"
                >
                    <Plus class="h-5 w-5" />
                    Añadir Ejercicio
                </button>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-1 flex-col items-center justify-center gap-6 py-12">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                    <Dumbbell class="h-10 w-10 text-muted-foreground" />
                </div>
                <div class="flex flex-col items-center gap-2 text-center">
                    <h2 class="text-xl font-semibold text-foreground">No hay ejercicios todavía</h2>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Comienza agregando ejercicios a esta rutina para crear un plan de entrenamiento completo.
                    </p>
                </div>
                <button
                    type="button"
                    @click="showAddDialog = true"
                    class="rounded-xl bg-primary px-8 py-3 text-base font-medium text-primary-foreground shadow-lg transition-all hover:bg-primary/90 hover:shadow-xl"
                >
                    <Plus class="mr-2 inline h-5 w-5" />
                    Agregar Primer Ejercicio
                </button>
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
