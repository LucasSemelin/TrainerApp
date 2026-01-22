<script setup lang="ts">
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

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        active: 'Activa',
        archived: 'Archivada',
    };
    return labels[status] || status;
};

const getStatusVariant = (status: string) => {
    const variants: Record<string, string> = {
        active: 'border-primary bg-primary/10 text-primary',
        archived: 'border-yellow-500/30 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    };
    return variants[status] || variants.active;
};

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

const showPill = (status: string) => {
    return status === 'active' || status === 'archived';
};
</script>

<template>
    <button
        @click="handleClick"
        class="group flex w-full flex-col gap-3 rounded-lg border border-border bg-card p-4 text-left transition-all hover:border-primary hover:bg-primary/5"
    >
        <!-- Primera línea: Nombre (2/3) + Pill (1/3) -->
        <div class="flex w-full items-start gap-3">
            <h3 class="flex-[2] font-medium text-foreground group-hover:text-primary">{{ workout.name }}</h3>
            <div v-if="showPill(workout.status)" class="flex flex-1 justify-end">
                <span :class="['rounded-full border px-3 py-1 text-xs font-medium whitespace-nowrap', getStatusVariant(workout.status)]">
                    {{ getStatusLabel(workout.status) }}
                </span>
            </div>
        </div>

        <!-- Segunda línea: Fecha de creación -->
        <p class="text-sm text-muted-foreground">Creada el {{ new Date(workout.created_at).toLocaleDateString('es-ES') }}</p>

        <!-- Tercera línea: Botones de acción -->
        <div v-if="workout.status === 'archived'" class="flex items-center gap-2" @click.stop>
            <!-- Estado normal: solo icono -->
            <button
                v-if="!showUnarchiveConfirm"
                @click="toggleUnarchiveConfirm"
                class="flex size-8 shrink-0 items-center justify-center rounded-full border border-yellow-500/30 bg-yellow-500/10 text-yellow-600 transition-colors hover:bg-yellow-500/20 dark:text-yellow-400"
                title="Desarchivar rutina"
            >
                <ArchiveRestore class="size-4" />
            </button>

            <!-- Estado de confirmación: texto + botones -->
            <div v-else class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">¿Desarchivar?</span>
                <button
                    @click="confirmUnarchive"
                    class="flex size-8 shrink-0 items-center justify-center rounded-full border border-green-500/30 bg-green-500/10 text-green-600 transition-colors hover:bg-green-500/20 dark:text-green-400"
                    title="Confirmar"
                >
                    <Check class="size-4" />
                </button>
                <button
                    @click="cancelUnarchive"
                    class="flex size-8 shrink-0 items-center justify-center rounded-full border border-red-500/30 bg-red-500/10 text-red-600 transition-colors hover:bg-red-500/20 dark:text-red-400"
                    title="Cancelar"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>
    </button>
</template>
