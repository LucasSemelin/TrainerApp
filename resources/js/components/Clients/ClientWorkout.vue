<script setup lang="ts">
import WorkoutStatus from '@/components/Workouts/WorkoutStatus.vue';
import type { Workout } from '@/types/workout';
import { ArchiveRestore, Check, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    workout: Workout;
    clientId: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    click: [workoutId: string];
    unarchive: [workoutId: string, event: Event];
}>();

const showUnarchiveConfirm = ref(false);

const handleClick = () => {
    if (!showUnarchiveConfirm.value) {
        emit('click', props.workout.id);
    }
};

const toggleUnarchiveConfirm = (event: Event) => {
    event.stopPropagation();
    showUnarchiveConfirm.value = !showUnarchiveConfirm.value;
};

const confirmUnarchive = (event: Event) => {
    event.stopPropagation();
    showUnarchiveConfirm.value = false;
    emit('unarchive', props.workout.id, event);
};

const cancelUnarchive = (event: Event) => {
    event.stopPropagation();
    showUnarchiveConfirm.value = false;
};
</script>

<template>
    <button
        @click="handleClick"
        class="group flex w-full flex-col gap-3 rounded-lg border border-border bg-card p-4 text-left transition-all hover:border-primary hover:bg-primary/5"
    >
        <!-- Primera línea: Nombre (2/3) + Pill (1/3) -->
        <div class="flex w-full items-start gap-3">
            <h3 class="flex-[2] text-lg font-medium text-foreground group-hover:text-primary">{{ workout.name }}</h3>
            <div class="flex flex-1 justify-end">
                <WorkoutStatus :workout="workout" />
            </div>
        </div>

        <!-- Segunda línea: Fecha de creación -->
        <p class="text-xs text-muted-foreground">Creada el {{ new Date(workout.created_at).toLocaleDateString('es-ES') }}</p>

        <!-- Tercera línea: Botones de acción -->
        <div v-if="workout.status === 'archived'" class="flex items-center gap-2" @click.stop>
            <!-- Estado normal: solo icono -->
            <button
                v-if="!showUnarchiveConfirm"
                @click="toggleUnarchiveConfirm"
                class="flex size-8 shrink-0 items-center justify-center rounded-full border border-warning/30 bg-warning/10 text-warning transition-colors hover:bg-warning/20"
                title="Desarchivar rutina"
            >
                <ArchiveRestore class="size-4" />
            </button>

            <!-- Estado de confirmación: texto + botones -->
            <div v-else class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">¿Desarchivar?</span>
                <button
                    @click="confirmUnarchive"
                    class="flex size-8 shrink-0 items-center justify-center rounded-full border border-success/30 bg-success/10 text-success transition-colors hover:bg-success/20"
                    title="Confirmar"
                >
                    <Check class="size-4" />
                </button>
                <button
                    @click="cancelUnarchive"
                    class="flex size-8 shrink-0 items-center justify-center rounded-full border border-destructive/30 bg-destructive/10 text-destructive transition-colors hover:bg-destructive/20"
                    title="Cancelar"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>
    </button>
</template>
