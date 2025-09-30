<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

interface Exercise {
    id: string;
    name: string;
}

interface Props {
    workoutId: string;
    open: boolean;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'created', exercise: any): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const exercises = ref<Exercise[]>([]);
const loadingExercises = ref(false);

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const form = useForm({
    exercise_id: '',
    sets: null as number | null,
    min_reps: null as number | null,
    max_reps: null as number | null,
    weight: null as number | null,
});

// Load exercises when dialog opens
const loadExercises = async () => {
    if (loadingExercises.value) return;

    loadingExercises.value = true;
    try {
        const response = await axios.get('/exercises/list');
        exercises.value = response.data;
    } catch (error) {
        console.error('Error loading exercises:', error);
        exercises.value = [];
    } finally {
        loadingExercises.value = false;
    }
};

// Watch for dialog opening
watch(
    () => props.open,
    (newValue) => {
        if (newValue && exercises.value.length === 0) {
            loadExercises();
        }
    },
);

const submit = async () => {
    // Clear previous errors
    form.clearErrors();
    form.processing = true;

    try {
        await axios.post(`/workouts/${props.workoutId}/exercises`, {
            exercise_id: form.exercise_id,
            sets: form.sets,
            min_reps: form.min_reps,
            max_reps: form.max_reps,
            weight: form.weight,
        });

        // Get the exercise name for the new exercise
        const selectedExercise = exercises.value.find((ex) => ex.id === form.exercise_id);

        const newExercise = {
            id: Date.now().toString(), // Temporary ID
            workout_id: props.workoutId,
            exercise_id: form.exercise_id,
            sets: form.sets,
            min_reps: form.min_reps,
            max_reps: form.max_reps,
            weight: form.weight,
            exercise: selectedExercise,
        };

        emit('created', newExercise);
        isOpen.value = false;
        form.reset();
    } catch (error: any) {
        // Handle validation errors
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach((key) => {
                if (key in form.data()) {
                    form.setError(key as keyof typeof form.data, errors[key][0]);
                }
            });
        } else {
            console.error('Error adding exercise:', error);
        }
    } finally {
        form.processing = false;
    }
};
</script>

<template>
    <div class="fixed inset-0 z-[100] flex items-center justify-center" v-if="isOpen">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80" @click="isOpen = false"></div>

        <!-- Dialog -->
        <div class="relative z-[101] mx-4 w-full max-w-lg">
            <div class="rounded-lg border bg-background shadow-lg">
                <!-- Header -->
                <div class="flex items-center justify-between border-b p-6">
                    <h2 class="text-lg font-semibold">Agregar Ejercicio</h2>
                    <button
                        type="button"
                        class="rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @click="isOpen = false"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <form @submit.prevent="submit" class="space-y-4 p-6">
                    <!-- Exercise Selector -->
                    <div>
                        <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"> Ejercicio </label>
                        <select
                            v-model="form.exercise_id"
                            :disabled="loadingExercises"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            required
                        >
                            <option value="" v-if="loadingExercises">Cargando ejercicios...</option>
                            <option value="" v-else>Seleccionar ejercicio</option>
                            <option v-for="exercise in exercises" :key="exercise.id" :value="exercise.id">
                                {{ exercise.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.exercise_id" class="mt-1 text-sm text-destructive">
                            {{ form.errors.exercise_id }}
                        </div>
                    </div>

                    <!-- Sets and Weight Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Sets -->
                        <div>
                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Series*
                            </label>
                            <input
                                v-model.number="form.sets"
                                type="number"
                                min="1"
                                placeholder="Ejemplo: 3"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            />
                            <div v-if="form.errors.sets" class="mt-1 text-sm text-destructive">
                                {{ form.errors.sets }}
                            </div>
                        </div>

                        <!-- Weight -->
                        <div>
                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Peso (kg)*
                            </label>
                            <input
                                v-model.number="form.weight"
                                type="number"
                                min="0"
                                step="0.5"
                                placeholder="Ejemplo: 20"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            />
                            <div v-if="form.errors.weight" class="mt-1 text-sm text-destructive">
                                {{ form.errors.weight }}
                            </div>
                        </div>
                    </div>

                    <!-- Repetitions Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Min Reps -->
                        <div>
                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Reps mínimas*
                            </label>
                            <input
                                v-model.number="form.min_reps"
                                type="number"
                                min="1"
                                placeholder="Ejemplo: 8"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            />
                            <div v-if="form.errors.min_reps" class="mt-1 text-sm text-destructive">
                                {{ form.errors.min_reps }}
                            </div>
                        </div>

                        <!-- Max Reps (Optional) -->
                        <div>
                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Reps máximas
                            </label>
                            <input
                                v-pnumber="form.max_reps"
                                type="number"
                                min="1"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Ejemplo: 12 (opcional)"
                            />
                            <div v-if="form.errors.max_reps" class="mt-1 text-sm text-destructive">
                                {{ form.errors.max_reps }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-2 pt-4">
                        <button
                            type="button"
                            @click="isOpen = false"
                            class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            {{ form.processing ? 'Agregando...' : 'Agregar Ejercicio' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
