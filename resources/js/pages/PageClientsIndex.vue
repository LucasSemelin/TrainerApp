<script setup lang="ts">
import ClientRemoveDialog from '@/components/ClientRemoveDialog.vue';
import ClientsCreateDialog from '@/components/ClientsCreateDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, Trash2 } from 'lucide-vue-next';
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
    <Head title="Clientes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 pb-24">
            <div class="flex items-center justify-between">
                <h1>Alumnos</h1>
                <ClientsCreateDialog @created="onCreated" />
            </div>

            <div class="flex justify-between font-medium text-slate-700 dark:text-slate-200">
                <div>Alumno</div>
                <div>Acciones</div>
            </div>

            <template v-for="(client, index) in clientsList" :key="client.id">
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
