<script setup lang="ts">
import ClientRemoveDialog from '@/components/ClientRemoveDialog.vue';
import ClientsCreateDialog from '@/components/ClientsCreateDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, Trash2, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Alumnos',
        href: clients.index().url,
    },
];

const page = usePage();
const clientsList = computed<Client[]>(() => page.props.clients as Client[]);

// client creation handled by ClientsCreateDialog component
const onCreated = () => {
    window.location.reload();
};
</script>

<template>
    <Head title="Alumnos" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 pb-24">
            <div v-if="clientsList.length > 0" class="flex items-center justify-end">
                <!-- <h1>Alumnos</h1> -->
                <ClientsCreateDialog @created="onCreated" />
            </div>

            <!-- Empty State -->
            <div v-if="clientsList.length === 0" class="flex flex-1 flex-col items-center justify-center gap-6 py-12">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                    <Users class="h-10 w-10 text-muted-foreground" />
                </div>
                <div class="flex flex-col items-center gap-2 text-center">
                    <h2 class="text-xl font-semibold text-foreground">No hay alumnos todavía</h2>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Comienza agregando tu primer alumno para poder crear y asignar rutinas de entrenamiento.
                    </p>
                </div>
                <ClientsCreateDialog @created="onCreated">
                    <template #trigger>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Agregar primer alumno
                        </button>
                    </template>
                </ClientsCreateDialog>
            </div>

            <!-- Clients List -->
            <template v-else v-for="(client, index) in clientsList" :key="client.id">
                <div
                    class="flex items-center justify-between border-b border-border/70 py-2 dark:border-border"
                    :class="index % 2 === 1 ? 'bg-muted/30' : ''"
                >
                    <div class="flex flex-col">
                        <span>
                            {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                        </span>
                        <span class="text-sm">{{ client.email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="clients.workouts.index(client.id).url"
                            class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                            aria-label="Ver rutinas"
                        >
                            <ClipboardList class="h-5 w-5" />
                        </Link>
                        <ClientRemoveDialog
                            :client-id="client.id"
                            :client-name="client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email"
                        >
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                    aria-label="Eliminar cliente"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </template>
                        </ClientRemoveDialog>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
