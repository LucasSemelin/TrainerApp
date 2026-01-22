<script setup lang="ts">
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { WorkoutSessionExercise } from '@/types/workout';
import { Dumbbell, MoreVertical } from 'lucide-vue-next';

interface Props {
    exercise: WorkoutSessionExercise;
}

defineProps<Props>();

const emit = defineEmits<{
    addSet: [exerciseId: string];
    editNotes: [exerciseId: string];
    delete: [exerciseId: string];
    deleteSet: [setId: string];
}>();

const handleDeleteSet = (setId: string) => {
    if (confirm('¿Estás seguro de que querés eliminar esta serie?')) {
        emit('deleteSet', setId);
    }
};
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-border bg-card">
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
                <div v-if="exercise.notes" class="mt-1">
                    <span class="text-sm text-muted-foreground italic">{{ exercise.notes }}</span>
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
                    <DropdownMenuItem @click="emit('addSet', exercise.id)"> Agregar serie </DropdownMenuItem>
                    <DropdownMenuItem @click="emit('editNotes', exercise.id)"> Editar notas </DropdownMenuItem>
                    <DropdownMenuItem> Detalles del ejercicio </DropdownMenuItem>
                    <DropdownMenuItem @click="emit('delete', exercise.id)" class="text-destructive focus:text-destructive">
                        Eliminar
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <!-- Sets Info -->
        <div class="border-t border-border/50">
            <!-- Sets Table -->
            <div v-if="exercise.sets && exercise.sets.length > 0">
                <div class="overflow-hidden">
                    <!-- Table Header -->
                    <div class="grid grid-cols-[60px_1fr_1fr_1fr_60px] gap-3 border-b border-border bg-muted/50 px-4 py-2 text-sm font-medium text-muted-foreground">
                        <div>Serie</div>
                        <div>Reps</div>
                        <div>Peso</div>
                        <div>Descanso</div>
                        <div></div>
                    </div>
                    <!-- Table Rows -->
                    <div v-for="(set, index) in exercise.sets" :key="set.id" class="grid grid-cols-[60px_1fr_1fr_1fr_60px] gap-3 border-b border-border px-4 py-3 text-sm last:border-b-0">
                        <div class="font-medium text-foreground">{{ index + 1 }}°</div>
                        <div class="text-muted-foreground">{{ set.target_reps || '-' }}</div>
                        <div class="text-muted-foreground">{{ set.target_weight ? `${Math.floor(set.target_weight)}kg` : '-' }}</div>
                        <div class="text-muted-foreground">
                            {{ set.rest_seconds ? `${Math.floor(set.rest_seconds / 60)}:${String(set.rest_seconds % 60).padStart(2, '0')}` : '-' }}
                        </div>
                        <div class="flex justify-end">
                            <button
                                @click="handleDeleteSet(set.id)"
                                class="inline-flex items-center justify-center rounded-md p-1 text-muted-foreground transition-colors hover:text-destructive"
                                title="Eliminar serie"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Add Set Button -->
                <div class="flex justify-end px-4 py-3">
                    <button @click="emit('addSet', exercise.id)" class="text-sm text-primary hover:text-primary/80 hover:underline">
                        + Agregar serie
                    </button>
                </div>
            </div>
            <!-- No sets message with button -->
            <div v-else class="flex items-center justify-between px-4 py-3">
                <div class="text-sm text-muted-foreground">Sin series</div>
                <button @click="emit('addSet', exercise.id)" class="text-sm text-primary hover:text-primary/80 hover:underline">
                    + Agregar serie
                </button>
            </div>
        </div>
    </div>
</template>
