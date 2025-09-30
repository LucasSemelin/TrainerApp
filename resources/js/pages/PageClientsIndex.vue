<script setup lang="ts">
import ClientsCreateDialog from '@/components/ClientsCreateDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, usePage } from '@inertiajs/vue3';
import { PlusCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1>Alumnos</h1>
                <ClientsCreateDialog @created="onCreated" />
            </div>

            <div class="flex justify-between font-medium text-slate-700 dark:text-slate-200">
                <div>Alumno</div>
                <div>Acciones</div>
            </div>

            <template v-for="client in clientsList" :key="client.id">
                <div class="flex items-center justify-between border-b border-border/70 py-2 dark:border-border">
                    <div class="flex flex-col">
                        <span>
                            {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                        </span>
                        <span class="text-sm">{{ client.email }}</span>
                    </div>
                    <button type="button" class="rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Agregar rutina">
                        <PlusCircle class="h-5 w-5 text-slate-700 dark:text-slate-200" />
                    </button>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
