<script setup lang="ts">
import WorkoutCreateDialog from '@/components/WorkoutCreateDialog.vue';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import type { Workout, WorkoutStatus } from '@/types/workout';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pin, Archive } from 'lucide-vue-next';
import { computed } from 'vue';

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

// Get active workout
const activeWorkout = computed(() => workouts.value.find((w) => w.status === 'active'));

// Get draft workouts
const draftWorkouts = computed(() => workouts.value.filter((w) => w.status === 'draft'));

// Get archived workouts
const archivedWorkouts = computed(() => workouts.value.filter((w) => w.status === 'archived'));

// Format date to DD/MM/YYYY
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

// Get status badge variant and text
const getStatusBadge = (status: WorkoutStatus) => {
    switch (status) {
        case 'active':
            return { text: 'Activa', variant: 'default' as const, class: 'border border-primary/40 bg-transparent text-primary' };
        case 'draft':
            return { text: 'Borrador', variant: 'secondary' as const, class: '' };
        case 'archived':
            return { text: 'Archivada', variant: 'outline' as const, class: '' };
        default:
            return { text: status, variant: 'outline' as const, class: '' };
    }
};

// Function to activate a workout
const activateWorkout = (workoutId: string) => {
    router.patch(
        `/clients/${client.value.id}/workouts/${workoutId}/activate`,
        {},
        {
            preserveScroll: true,
        },
    );
};

// Function to archive a workout
const archiveWorkout = (workoutId: string) => {
    router.patch(
        `/clients/${client.value.id}/workouts/${workoutId}/archive`,
        {},
        {
            preserveScroll: true,
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

            <!-- Lista de rutinas -->
            <div v-if="workouts.length > 0" class="space-y-4">
                <!-- Rutina activa -->
                <div v-if="activeWorkout">
                    <Link
                        :href="clients.workouts.show({ client: client.id, workout: activeWorkout.id }).url"
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
                            <span class="text-xs text-muted-foreground">
                                {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">
                                    {{ activeWorkout.name }}
                                </span>
                                <Badge :variant="getStatusBadge('active').variant" :class="getStatusBadge('active').class">
                                    {{ getStatusBadge('active').text }}
                                </Badge>
                            </div>
                            <span class="text-sm text-muted-foreground"> Creada el {{ formatDate(activeWorkout.created_at) }} </span>
                        </div>
                        <button
                            @click.prevent="archiveWorkout(activeWorkout.id)"
                            class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-amber-50 hover:text-amber-600 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-amber-900/20"
                            aria-label="Archivar rutina"
                            title="Archivar rutina"
                        >
                            <Archive class="h-5 w-5" />
                        </button>
                    </Link>
                </div>

                <!-- Rutinas en borrador -->
                <div v-if="draftWorkouts.length > 0">
                    <h3 class="mb-2 text-sm font-medium text-muted-foreground">Borradores</h3>
                    <div class="space-y-2">
                        <div
                            v-for="workout in draftWorkouts"
                            :key="workout.id"
                            class="relative flex items-center justify-between border-b border-border/70 py-2 dark:border-border"
                        >
                            <Link
                                :href="clients.workouts.show({ client: client.id, workout: workout.id }).url"
                                class="-mx-2 flex flex-1 cursor-pointer flex-col rounded-md px-2 py-1 transition-colors hover:bg-accent/50"
                            >
                                <span class="text-xs text-muted-foreground">
                                    {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        {{ workout.name }}
                                    </span>
                                    <Badge :variant="getStatusBadge('draft').variant" :class="getStatusBadge('draft').class">
                                        {{ getStatusBadge('draft').text }}
                                    </Badge>
                                </div>
                                <span class="text-sm text-muted-foreground"> Creada el {{ formatDate(workout.created_at) }} </span>
                            </Link>
                            <div class="z-10 flex items-center gap-2">
                                <button
                                    @click.stop="activateWorkout(workout.id)"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-green-50 hover:text-green-600 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-green-900/20"
                                    aria-label="Activar rutina"
                                    title="Activar como rutina actual"
                                >
                                    <Pin class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rutinas archivadas -->
                <div v-if="archivedWorkouts.length > 0">
                    <h3 class="mb-2 text-sm font-medium text-muted-foreground">Anteriores</h3>
                    <div class="space-y-2">
                        <div
                            v-for="workout in archivedWorkouts"
                            :key="workout.id"
                            class="relative flex items-center justify-between border-b border-border/70 py-2 dark:border-border"
                        >
                            <Link
                                :href="clients.workouts.show({ client: client.id, workout: workout.id }).url"
                                class="-mx-2 flex flex-1 cursor-pointer flex-col rounded-md px-2 py-1 transition-colors hover:bg-accent/50"
                            >
                                <span class="text-xs text-muted-foreground">
                                    {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        {{ workout.name }}
                                    </span>
                                </div>
                                <span class="text-sm text-muted-foreground"> Creada el {{ formatDate(workout.created_at) }} </span>
                            </Link>
                            <div class="z-10 flex items-center gap-2">
                                <button
                                    @click.stop="activateWorkout(workout.id)"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-green-50 hover:text-green-600 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-green-900/20"
                                    aria-label="Reactivar rutina"
                                    title="Reactivar esta rutina"
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
