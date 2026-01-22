<script setup lang="ts">
import type { Workout } from '@/types/workout';

interface Props {
    workout: Workout;
}

defineProps<Props>();

const getStatusLabel = (workout: Workout): string => {
    if (workout.is_current) {
        return 'Actual';
    }

    const labels: Record<string, string> = {
        active: 'Activa',
        draft: 'Borrador',
        archived: 'Archivada',
    };
    return labels[workout.status] || workout.status;
};

const getStatusVariant = (workout: Workout): string => {
    if (workout.is_current) {
        return 'border-primary bg-primary/10 text-primary';
    }

    const variants: Record<string, string> = {
        active: 'border-info/30 bg-info/10 text-info',
        draft: 'border-draft/30 bg-draft/10 text-draft',
        archived: 'border-warning/30 bg-warning/10 text-warning',
    };
    return variants[workout.status] || variants.active;
};
</script>

<template>
    <span :class="['rounded-full border px-3 py-1 text-xs font-medium whitespace-nowrap', getStatusVariant(workout)]">
        {{ getStatusLabel(workout) }}
    </span>
</template>
