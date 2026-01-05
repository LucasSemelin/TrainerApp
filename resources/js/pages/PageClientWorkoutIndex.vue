<script setup lang="ts">
import WorkoutCreateDialog from '@/components/WorkoutCreateDialog.vue';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pin } from 'lucide-vue-next';
import { computed } from 'vue';

interface Workout {
    id: string;
    name: string;
    trainer_id: string | null;
    client_id: string;
    is_current: boolean;
    created_at: string;
    updated_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Alumnos',
        href: clients.index().url,
    },
    {
        title: 'Rutinas',
        href: '#',
    },
];

const page = usePage();
const client = computed<Client>(() => page.props.client as Client);
const workouts = computed<Workout[]>(() => page.props.workouts as Workout[]);

// Check if there's a current workout
const hasCurrentWorkout = computed(() => workouts.value.some((w) => w.is_current));

// Get the most recent workout when there's no current workout
const mostRecentWorkout = computed(() => {
    if (hasCurrentWorkout.value || workouts.value.length === 0) return null;
    return [...workouts.value].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())[0];
});

// Get remaining workouts (excluding current and most recent if applicable)
const remainingWorkouts = computed(() => {
    if (hasCurrentWorkout.value) {
        return workouts.value.filter((w) => !w.is_current);
    }
    if (mostRecentWorkout.value) {
        return workouts.value.filter((w) => w.id !== mostRecentWorkout.value?.id);
    }
    return workouts.value;
});

// Format date to DD/MM/YYYY
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

// Function to mark a workout as current
const makeWorkoutCurrent = (workoutId: string) => {
    router.patch(
        `/clients/${client.value.id}/workouts/${workoutId}/make-current`,
        {},
        {
            onSuccess: () => {
                // Optional: Show success message
            },
            onError: () => {
                // Optional: Show error message
            },
        },
    );
};
</script>

<template>
    <Head title="Rutinas del Alumno" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 pb-24">
            <div class="flex items-center justify-between">
                <h1>Rutinas de {{ client.profile ? `${client.profile.first_name}` : client.email }}</h1>
                <WorkoutCreateDialog :client-id="client.id" />
            </div>

            <!-- <div class="flex justify-between font-medium text-slate-700 dark:text-slate-200">
                <div>Rutinas</div>
                <div>Acciones</div>
            </div> -->

            <!-- Lista de rutinas -->
            <div v-if="workouts.length > 0" class="space-y-4">
                <!-- Rutina actual -->
                <div v-if="hasCurrentWorkout">
                    <div v-for="workout in workouts.filter((w) => w.is_current)" :key="workout.id">
                        <Link
                            :href="clients.workouts.show({ client: client.id, workout: workout.id }).url"
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-primary/60 px-4 py-3 transition-all hover:border-primary/80"
                            style="
                                background: repeating-linear-gradient(
                                    135deg,
                                    rgba(59, 130, 246, 0.08) 0px,
                                    rgba(59, 130, 246, 0.08) 8px,
                                    transparent 8px,
                                    transparent 16px
                                );
                            "
                        >
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        {{ workout.name }}
                                    </span>
                                    <Badge variant="secondary" class="border border-primary/40 bg-transparent text-xs text-primary">Actual</Badge>
                                </div>
                                <span class="text-sm text-muted-foreground"> Creada el {{ formatDate(workout.created_at) }} </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Rutina más reciente cuando no hay actual -->
                <div v-if="!hasCurrentWorkout && mostRecentWorkout" :key="mostRecentWorkout.id">
                    <Link
                        :href="clients.workouts.show({ client: client.id, workout: mostRecentWorkout.id }).url"
                        class="flex cursor-pointer items-center justify-between rounded-lg border-2 border-primary bg-muted/50 px-4 py-3 transition-all hover:border-primary/80 hover:bg-muted/70 dark:bg-muted/30 dark:hover:bg-muted/40"
                    >
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">
                                    {{ mostRecentWorkout.name }}
                                </span>
                                <Badge variant="secondary" class="text-xs"> Más reciente </Badge>
                            </div>
                            <span class="text-sm text-muted-foreground"> Creada el {{ formatDate(mostRecentWorkout.created_at) }} </span>
                        </div>
                    </Link>
                </div>

                <!-- Rutinas anteriores -->
                <div v-if="remainingWorkouts.length > 0">
                    <h3 class="mb-2 text-sm font-medium text-muted-foreground">Rutinas anteriores</h3>
                    <div class="space-y-2">
                        <div
                            v-for="workout in remainingWorkouts"
                            :key="workout.id"
                            class="relative flex items-center justify-between border-b border-border/70 py-2 dark:border-border"
                        >
                            <Link
                                :href="clients.workouts.show({ client: client.id, workout: workout.id }).url"
                                class="-mx-2 flex flex-1 cursor-pointer flex-col rounded-md px-2 py-1 transition-colors hover:bg-accent/50"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        {{ workout.name }}
                                    </span>
                                </div>
                                <span class="text-sm text-muted-foreground"> Creada el {{ formatDate(workout.created_at) }} </span>
                            </Link>
                            <div class="z-10 flex items-center gap-2">
                                <button
                                    @click.stop="makeWorkoutCurrent(workout.id)"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-green-50 hover:text-green-600 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-green-900/20"
                                    aria-label="Marcar como actual"
                                    title="Marcar como rutina actual"
                                >
                                    <Pin class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje cuando no hay rutinas -->
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                <p class="text-muted-foreground">No hay rutinas creadas para este alumno.</p>
            </div>
        </div>
    </AppLayout>
</template>
