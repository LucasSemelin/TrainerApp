<script setup lang="ts">
import ClientWorkout from '@/components/Clients/ClientWorkout.vue';
import WorkoutCreateDialog from '@/components/WorkoutCreateDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import type { Workout } from '@/types/workout';
import { Head, router, usePage } from '@inertiajs/vue3';
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

const navigateToWorkout = (workoutId: string) => {
    router.visit(clients.workouts.show({ client: client.value.id, workout: workoutId }).url);
};

const unarchiveWorkout = (workoutId: string) => {
    router.patch(
        `/clients/${client.value.id}/workouts/${workoutId}/unarchive`,
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
            <div v-if="workouts.length > 0" class="space-y-3">
                <ClientWorkout
                    v-for="workout in workouts"
                    :key="workout.id"
                    :workout="workout"
                    :client-id="client.id"
                    @click="navigateToWorkout"
                    @unarchive="unarchiveWorkout"
                />
            </div>

            <!-- Mensaje cuando no hay rutinas -->
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                <p class="text-muted-foreground">No hay rutinas creadas para este alumno.</p>
            </div>
        </div>
    </AppLayout>
</template>
