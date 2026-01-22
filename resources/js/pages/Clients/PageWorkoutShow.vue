<script setup lang="ts">
import ExerciseSetCreateDialog from '@/components/ExerciseSetCreateDialog.vue';
import SessionNavigation from '@/components/SessionNavigation.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import WorkoutExercisesAddDialog from '@/components/WorkoutExercisesAddDialog.vue';
import WorkoutExercise from '@/components/Workouts/WorkoutExercise.vue';
import WorkoutStatus from '@/components/Workouts/WorkoutStatus.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import type { Workout, WorkoutSession, WorkoutSessionExercise, WorkoutSessionExerciseSet } from '@/types/workout';
import { Head, usePage } from '@inertiajs/vue3';
import { ArchiveRestore, Dumbbell, PencilLine, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const client = computed<Client>(() => page.props.client as Client);
const workout = computed<Workout>(() => page.props.workout as Workout);

// Use reactive ref for sessions to allow dynamic updates
const sessions = ref<WorkoutSession[]>(page.props.sessions as WorkoutSession[]);

// Active session state
const activeSessionId = ref<string>(sessions.value[0]?.id || '');

// Get the active session
const activeSession = computed(() => sessions.value.find((s) => s.id === activeSessionId.value));

// Get exercises for the active session
const activeExercises = computed(() => activeSession.value?.exercises || []);

// Dialog state
const showAddDialog = ref(false);
const showSetDialog = ref(false);
const showDeleteConfirmDialog = ref(false);
const showEditNotesDialog = ref(false);
const showAddSessionDialog = ref(false);
const selectedExerciseId = ref<string>('');
const setToDelete = ref<{ setId: string; exerciseId: string } | null>(null);
const setToEdit = ref<{ setId: string; exerciseId: string; currentNotes: string } | null>(null);
const notesText = ref<string>('');
const newSessionName = ref<string>('');

// Get existing sets for selected exercise
const selectedExerciseSets = computed(() => {
    const exercise = activeExercises.value.find((ex) => ex.id === selectedExerciseId.value);
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

const unarchiveWorkout = () => {
    if (confirm('¿Deseas desarchivar esta rutina?')) {
        const form = {
            _method: 'PATCH',
        };
        fetch(`/clients/${client.value.id}/workouts/${workout.value.id}/unarchive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form),
        }).then(() => {
            window.location.reload();
        });
    }
};

// Danger Zone Functions
const deleteCurrentSession = () => {
    if (!activeSession.value) return;

    if (confirm(`¿Estás seguro de que querés eliminar "${activeSession.value.name || `Día ${activeSession.value.session_order}`}"?`)) {
        fetch(`/clients/${client.value.id}/workouts/${workout.value.id}/sessions/${activeSession.value.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        }).then(() => {
            window.location.reload();
        });
    }
};

const archiveWorkout = () => {
    if (confirm('¿Estás seguro de que querés archivar esta rutina?')) {
        fetch(`/clients/${client.value.id}/workouts/${workout.value.id}/archive`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        }).then(() => {
            window.location.reload();
        });
    }
};

const deleteWorkout = () => {
    if (confirm('⚠️ ATENCIÓN: Esta acción no se puede deshacer. ¿Estás seguro de que querés eliminar esta rutina permanentemente?')) {
        fetch(`/clients/${client.value.id}/workouts/${workout.value.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        }).then(() => {
            window.location.href = clients.workouts.index({ client: client.value.id }).url;
        });
    }
};

// Handle session selection
const onSessionSelect = (sessionId: string) => {
    activeSessionId.value = sessionId;
};

// Handle adding a new session
const onAddSession = () => {
    const nextOrder = Math.max(...sessions.value.map((s) => s.session_order), 0) + 1;
    newSessionName.value = `Día ${nextOrder}`;
    showAddSessionDialog.value = true;
};

// Create new session
const createSession = async () => {
    try {
        const response = await fetch(`/workouts/${workout.value.id}/sessions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                name: newSessionName.value || null,
            }),
        });

        if (response.ok) {
            const data = await response.json();
            const newSession: WorkoutSession = {
                ...data.session,
                exercises: [],
            };
            sessions.value.push(newSession);
            activeSessionId.value = newSession.id;
        } else {
            alert('Error al crear la sesión');
        }
    } catch (error) {
        console.error('Error al crear la sesión:', error);
        alert('Error al crear la sesión');
    } finally {
        showAddSessionDialog.value = false;
        newSessionName.value = '';
    }
};

// Handle exercise added
const onExerciseAdded = (newExercise: WorkoutSessionExercise) => {
    const session = sessions.value.find((s) => s.id === activeSessionId.value);
    if (session) {
        if (!session.exercises) {
            session.exercises = [];
        }
        session.exercises.push(newExercise);
    }
};

// Handle opening set dialog
const openSetDialog = (exerciseId: string) => {
    selectedExerciseId.value = exerciseId;
    showSetDialog.value = true;
};

// Handle edit notes for an exercise
const editExerciseNotes = (exerciseId: string) => {
    const exercise = activeExercises.value.find((ex) => ex.id === exerciseId);

    setToEdit.value = {
        setId: '',
        exerciseId,
        currentNotes: exercise?.notes || '',
    };
    notesText.value = exercise?.notes || '';
    showEditNotesDialog.value = true;
};

// Save edited notes - This now updates the session exercise notes, not set notes
const saveEditedNotes = async () => {
    if (!setToEdit.value) return;

    const { exerciseId } = setToEdit.value;

    try {
        const response = await fetch(`/workout-session-exercises/${exerciseId}`, {
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
            const session = sessions.value.find((s) => s.id === activeSessionId.value);
            const exercise = session?.exercises?.find((ex) => ex.id === exerciseId);
            if (exercise) {
                exercise.notes = notesText.value;
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
const onSetCreated = (newSet: WorkoutSessionExerciseSet) => {
    const session = sessions.value.find((s) => s.id === activeSessionId.value);
    const exercise = session?.exercises?.find((ex) => ex.id === selectedExerciseId.value);
    if (exercise) {
        if (!exercise.sets) {
            exercise.sets = [];
        }
        exercise.sets.push(newSet);
        exercise.sets.sort((a, b) => a.set_order - b.set_order);
    }
};

// Confirm set deletion
const confirmDeleteSet = async () => {
    if (!setToDelete.value) return;

    const { setId, exerciseId } = setToDelete.value;

    try {
        const response = await fetch(`/workout-session-exercise-sets/${setId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const session = sessions.value.find((s) => s.id === activeSessionId.value);
            const exercise = session?.exercises?.find((ex) => ex.id === exerciseId);
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

// Delete set directly (from WorkoutExercise component)
const deleteSet = async (setId: string) => {
    try {
        const response = await fetch(`/workout-session-exercise-sets/${setId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            // Find and update the exercise's sets
            const session = sessions.value.find((s) => s.id === activeSessionId.value);
            if (session && session.exercises) {
                for (const exercise of session.exercises) {
                    if (exercise.sets) {
                        const setIndex = exercise.sets.findIndex((set) => set.id === setId);
                        if (setIndex !== -1) {
                            const deletedSetOrder = exercise.sets[setIndex].set_order;
                            
                            // Eliminar la serie
                            exercise.sets.splice(setIndex, 1);
                            
                            // Reordenar las series restantes (decrementar el order de las que venían después)
                            exercise.sets.forEach((set) => {
                                if (set.set_order > deletedSetOrder) {
                                    set.set_order--;
                                }
                            });
                            
                            break;
                        }
                    }
                }
            }
        } else {
            alert('Error al eliminar la serie');
        }
    } catch (error) {
        console.error('Error al eliminar la serie:', error);
        alert('Error al eliminar la serie');
    }
};

// Delete exercise from session
const deleteExercise = async (exerciseId: string) => {
    if (!confirm('¿Estás seguro de que querés eliminar este ejercicio?')) return;

    try {
        const response = await fetch(`/workout-session-exercises/${exerciseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const session = sessions.value.find((s) => s.id === activeSessionId.value);
            if (session && session.exercises) {
                session.exercises = session.exercises.filter((ex) => ex.id !== exerciseId);
            }
        } else {
            alert('Error al eliminar el ejercicio');
        }
    } catch (error) {
        console.error('Error al eliminar el ejercicio:', error);
        alert('Error al eliminar el ejercicio');
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
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                        </span>
                        <WorkoutStatus :workout="workout" />
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <h1 class="text-2xl font-semibold">{{ workout.name }}</h1>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md p-1 text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <PencilLine class="h-4 w-4" />
                        </button>
                    </div>
                    <div v-if="workout.status === 'archived'" class="mt-2">
                        <Button @click="unarchiveWorkout" variant="outline" size="sm" class="gap-2">
                            <ArchiveRestore class="h-4 w-4" />
                            Desarchivar
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Session Navigation -->
            <SessionNavigation :sessions="sessions" :active-session-id="activeSessionId" @select="onSessionSelect" @add="onAddSession" />

            <!-- Active Session Name -->
            <div v-if="activeSession" class="flex items-center gap-2">
                <h2 class="text-lg font-medium">{{ activeSession.name || `Día ${activeSession.session_order}` }}</h2>
            </div>

            <!-- Exercises List -->
            <div v-if="activeExercises.length > 0" class="space-y-4">
                <!-- Exercise Card -->
                <WorkoutExercise
                    v-for="exercise in activeExercises"
                    :key="exercise.id"
                    :exercise="exercise"
                    @add-set="openSetDialog"
                    @edit-notes="editExerciseNotes"
                    @delete="deleteExercise"
                    @delete-set="deleteSet"
                />

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
                        Comienza agregando ejercicios a esta sesión para crear un plan de entrenamiento completo.
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

            <!-- Danger Zone -->
            <div class="mt-8 rounded-lg border-2 border-destructive/30 bg-destructive/5 p-6">
                <h2 class="mb-4 text-lg font-semibold text-destructive">Zona de peligro</h2>
                <div class="space-y-3">
                    <!-- Delete Current Session -->
                    <div v-if="activeSession" class="rounded-md border border-border bg-card p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-foreground">Eliminar día actual</h3>
                            <Button @click="deleteCurrentSession" variant="destructive" size="sm"> Eliminar día </Button>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Eliminar permanentemente "{{ activeSession.name || `Día ${activeSession.session_order}` }}"
                        </p>
                    </div>

                    <!-- Archive Workout -->
                    <div v-if="workout.status !== 'archived'" class="rounded-md border border-border bg-card p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-foreground">Archivar rutina</h3>
                            <Button @click="archiveWorkout" variant="outline" size="sm" class="border-warning text-warning hover:bg-warning/10">
                                Archivar
                            </Button>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">La rutina dejará de estar disponible para el alumno</p>
                    </div>

                    <!-- Delete Workout -->
                    <div class="rounded-md border border-border bg-card p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-foreground">Eliminar rutina</h3>
                            <Button @click="deleteWorkout" variant="destructive" size="sm"> Eliminar rutina </Button>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">Eliminar permanentemente esta rutina y todos sus datos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Exercise Dialog -->
        <WorkoutExercisesAddDialog v-model:open="showAddDialog" :session-id="activeSessionId" @created="onExerciseAdded" />

        <!-- Add Set Dialog -->
        <ExerciseSetCreateDialog
            v-model:open="showSetDialog"
            :session-exercise-id="selectedExerciseId"
            :existing-sets="selectedExerciseSets"
            @created="onSetCreated"
        />

        <!-- Add Session Dialog -->
        <Dialog v-model:open="showAddSessionDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Agregar Sesión</DialogTitle>
                    <DialogDescription> Crea una nueva sesión (día) para esta rutina. </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="session-name">Nombre de la sesión</Label>
                        <Input id="session-name" v-model="newSessionName" placeholder="Ej: Día 2, Push Day, etc." />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showAddSessionDialog = false"> Cancelar </Button>
                    <Button @click="createSession"> Crear Sesión </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Notes Dialog -->
        <Dialog v-model:open="showEditNotesDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Editar Notas</DialogTitle>
                    <DialogDescription> Agrega o modifica las notas para este ejercicio. </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="notes">Notas</Label>
                        <textarea
                            id="notes"
                            v-model="notesText"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Escribe las notas para este ejercicio..."
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
