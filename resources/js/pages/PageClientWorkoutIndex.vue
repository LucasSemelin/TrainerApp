<script setup lang="ts">
import WorkoutCreateDialog from '@/components/WorkoutCreateDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { computed } from 'vue';

interface Workout {
    id: string;
    trainer_id: string | null;
    client_id: string;
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

// Format date to DD/MM/YYYY
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
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

            <div class="flex justify-between font-medium text-slate-700 dark:text-slate-200">
                <div>Rutinas</div>
                <div>Acciones</div>
            </div>

            <!-- Lista de rutinas -->
            <div v-if="workouts.length > 0" class="space-y-2">
                <div
                    v-for="workout in workouts"
                    :key="workout.id"
                    class="flex items-center justify-between border-b border-border/70 py-2 dark:border-border"
                >
                    <div class="flex flex-col">
                        <span class="font-medium">
                            {{ formatDate(workout.created_at) }}
                        </span>
                        <!-- <span class="text-sm text-muted-foreground"> Rutina creada </span> -->
                    </div>
                    <Link
                        :href="clients.workouts.show({ client: client.id, workout: workout.id }).url"
                        class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        aria-label="Ver rutina"
                    >
                        <Eye class="h-5 w-5" />
                    </Link>
                </div>
            </div>

            <!-- Mensaje cuando no hay rutinas -->
            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                <p class="text-muted-foreground">No hay rutinas creadas para este alumno.</p>
            </div>
        </div>
    </AppLayout>
</template>
