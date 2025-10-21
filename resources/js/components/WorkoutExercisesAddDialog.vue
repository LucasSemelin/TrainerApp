<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { Check, Loader2, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Exercise {
    id: string;
    name: string;
    alternative_names: string[];
    slug: string;
    description?: string;
    categories?: string[];
    matched_name?: string; // El nombre que coincidió con la búsqueda
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

const searchQuery = ref('');
const searchResults = ref<Exercise[]>([]);
const loadingSearch = ref(false);
const selectedExercise = ref<Exercise | null>(null);
const showResults = ref(false);
const inputFocused = ref(false);

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const form = useForm({
    exercise_id: '',
});

// Search function
const performSearch = async (query: string) => {
    if (!query.trim() || query.length < 2) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    loadingSearch.value = true;
    try {
        const response = await axios.get(`/exercises/search?q=${encodeURIComponent(query)}`);
        searchResults.value = response.data;
        showResults.value = true;
    } catch (error) {
        console.error('Error searching exercises:', error);
        searchResults.value = [];
        showResults.value = false;
    } finally {
        loadingSearch.value = false;
    }
};

// Debounced search function
const searchExercises = useDebounceFn(performSearch, 300);

// Handle search input
const handleSearchInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    searchQuery.value = target.value;
    searchExercises(target.value);
};

// Handle input focus
const handleInputFocus = () => {
    inputFocused.value = true;
    showResults.value = searchResults.value.length > 0;
};

// Handle input blur
const handleInputBlur = () => {
    // Delay to allow click on results
    setTimeout(() => {
        inputFocused.value = false;
    }, 200);
};

// Select an exercise
const selectExercise = (exercise: Exercise) => {
    // Determinar el nombre que se está mostrando y guardarlo
    const displayName = getDisplayName(exercise);
    const exerciseWithMatchedName = {
        ...exercise,
        matched_name: displayName,
    };

    selectedExercise.value = exerciseWithMatchedName;
    form.exercise_id = exercise.id;
    searchQuery.value = displayName;
    showResults.value = false;
};

// Get the display name for an exercise based on search
const getDisplayName = (exercise: Exercise) => {
    if (exercise.matched_name) {
        return exercise.matched_name;
    }

    // Si hay una búsqueda activa, buscar el nombre que mejor coincida
    if (searchQuery.value && searchQuery.value.length >= 2) {
        const query = searchQuery.value.toLowerCase();

        // Buscar coincidencia exacta primero
        const exactMatch = exercise.alternative_names.find((name) => name.toLowerCase() === query);
        if (exactMatch) return exactMatch;

        // Buscar coincidencia parcial al inicio
        const startMatch = exercise.alternative_names.find((name) => name.toLowerCase().startsWith(query));
        if (startMatch) return startMatch;

        // Buscar coincidencia parcial en cualquier lugar
        const partialMatch = exercise.alternative_names.find((name) => name.toLowerCase().includes(query));
        if (partialMatch) return partialMatch;
    }

    // Por defecto, usar el nombre principal
    return exercise.name;
};

// Get alternative names excluding the matched name
const getAlternativeNames = (exercise: Exercise) => {
    const displayName = getDisplayName(exercise);
    return exercise.alternative_names.filter((name) => name !== displayName);
};

// Clear selection
const clearSelection = () => {
    selectedExercise.value = null;
    form.exercise_id = '';
    searchQuery.value = '';
    searchResults.value = [];
    showResults.value = false;
};

// Prevent scroll propagation on mobile
const handleResultsScroll = (event: Event) => {
    event.stopPropagation();
};

// Handle touch events to prevent scroll propagation on mobile
const handleTouchStart = (event: TouchEvent) => {
    // Store the initial touch position
    const target = event.currentTarget as HTMLElement;
    const touch = event.touches[0];
    (target as any)._startY = touch.clientY;
    (target as any)._startScrollTop = target.scrollTop;
};

const handleTouchMove = (event: TouchEvent) => {
    const target = event.currentTarget as HTMLElement;
    const touch = event.touches[0];
    const startY = (target as any)._startY;
    const startScrollTop = (target as any)._startScrollTop;
    
    if (!startY) return;
    
    const deltaY = touch.clientY - startY;
    const newScrollTop = startScrollTop - deltaY;
    
    // If we're at the boundaries and trying to scroll beyond, prevent the event
    if (
        (newScrollTop <= 0 && deltaY > 0) || // At top, trying to scroll up
        (newScrollTop >= target.scrollHeight - target.clientHeight && deltaY < 0) // At bottom, trying to scroll down
    ) {
        event.preventDefault();
        event.stopPropagation();
        return;
    }
    
    // Otherwise, allow the scroll but prevent propagation
    event.stopPropagation();
};

// Watch for dialog opening/closing
watch(
    () => props.open,
    (newValue) => {
        if (!newValue) {
            // Reset when dialog closes
            clearSelection();
        }
    },
);

const submit = async () => {
    // Clear previous errors
    form.clearErrors();
    form.processing = true;

    try {
        const response = await axios.post(`/workouts/${props.workoutId}/exercises`, {
            exercise_id: form.exercise_id,
        });

        // Use the real exercise workout data from the backend
        const exerciseWorkout = response.data.exercise_workout;

        emit('created', exerciseWorkout);
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
    <Dialog :open="isOpen" @update:open="(value: boolean) => (isOpen = value)">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Agregar Ejercicio</DialogTitle>
                <DialogDescription> Busca el ejercicio que deseas agregar al entrenamiento </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Exercise Search -->
                <div class="space-y-2">
                    <Label for="exercise-search">Buscar Ejercicio</Label>
                    <div class="relative">
                        <Search class="absolute top-3 left-3 h-4 w-4 text-muted-foreground" />
                        <Input
                            id="exercise-search"
                            v-model="searchQuery"
                            placeholder="Ej: press de banca, sentadilla, dominadas..."
                            class="pl-10"
                            @input="handleSearchInput"
                            @focus="handleInputFocus"
                            @blur="handleInputBlur"
                        />
                        <div v-if="loadingSearch" class="absolute top-3 right-3">
                            <Loader2 class="h-4 w-4 animate-spin text-muted-foreground" />
                        </div>
                    </div>

                    <!-- Selected Exercise Display -->
                    <div
                        v-if="selectedExercise"
                        class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-950"
                    >
                        <div class="flex items-center gap-2">
                            <Check class="h-4 w-4 text-green-600 dark:text-green-400" />
                            <div>
                                <div class="font-medium text-green-800 dark:text-green-200">{{ getDisplayName(selectedExercise) }}</div>
                                <div v-if="getAlternativeNames(selectedExercise).length > 0" class="text-sm text-green-600 dark:text-green-400">
                                    También:
                                    {{ getAlternativeNames(selectedExercise).slice(0, 2).join(', ') }}
                                </div>
                            </div>
                        </div>
                        <Button type="button" variant="ghost" size="sm" @click="clearSelection">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </Button>
                    </div>

                    <!-- Search Results -->
                    <div
                        v-if="showResults && searchResults.length > 0 && !selectedExercise"
                        class="max-h-[300px] overflow-y-auto rounded-lg border bg-background overscroll-contain touch-pan-y"
                        :class="{ 'scroll-smooth': inputFocused }"
                        @scroll="handleResultsScroll"
                        @touchstart="handleTouchStart"
                        @touchmove="handleTouchMove"
                    >
                        <div
                            v-for="exercise in searchResults"
                            :key="exercise.id"
                            @click="selectExercise(exercise)"
                            class="cursor-pointer border-b p-3 transition-colors last:border-b-0 hover:bg-accent"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium">{{ getDisplayName(exercise) }}</div>
                                    <div v-if="getAlternativeNames(exercise).length > 0" class="mt-1 text-sm text-muted-foreground">
                                        También conocido como: {{ getAlternativeNames(exercise).join(', ') }}
                                    </div>
                                </div>
                                <div v-if="exercise.categories && exercise.categories.length > 0" class="ml-3 flex-shrink-0">
                                    <Badge variant="secondary">
                                        {{ exercise.categories.join(', ') }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Results -->
                    <div
                        v-else-if="showResults && searchResults.length === 0 && searchQuery && !loadingSearch"
                        class="rounded-lg border border-dashed py-8 text-center text-muted-foreground"
                    >
                        No se encontraron ejercicios que coincidan con "{{ searchQuery }}"
                    </div>

                    <!-- Validation Error -->
                    <div v-if="form.errors.exercise_id" class="text-sm text-destructive">
                        {{ form.errors.exercise_id }}
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-2 pt-4">
                    <Button type="button" variant="outline" @click="isOpen = false"> Cancelar </Button>
                    <Button type="submit" :disabled="form.processing || !selectedExercise">
                        {{ form.processing ? 'Agregando...' : 'Agregar Ejercicio' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
