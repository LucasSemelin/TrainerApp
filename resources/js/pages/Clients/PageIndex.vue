<script setup lang="ts">
import ClientsCreateDialog from '@/components/ClientsCreateDialog.vue';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import clients from '@/routes/clients';
import { BreadcrumbItem } from '@/types';
import type { Client } from '@/types/client';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
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

function goToClientShow(clientId: string) {
    router.visit(`/clients/${clientId}`);
}
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
                    <h2 class="text-xl font-semibold text-foreground">No tenés alumnos todavía</h2>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Comenzá agregando tu primer alumno para poder crearle y asignarle rutinas de entrenamiento.
                    </p>
                </div>
                <ClientsCreateDialog @created="onCreated">
                    <template #trigger>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Agregar alumno
                        </button>
                    </template>
                </ClientsCreateDialog>
            </div>

            <!-- Clients List -->
            <template v-else v-for="(client, index) in clientsList" :key="client.id">
                <div
                    class="group flex cursor-pointer items-center justify-between border-b border-border/70 py-2 transition-colors hover:bg-muted/40 dark:border-border"
                    :class="index % 2 === 1 ? 'bg-muted/30' : ''"
                    @click="goToClientShow(client.id)"
                >
                    <div class="flex w-full flex-col">
                        <span class="flex items-center gap-2">
                            {{ client.profile ? `${client.profile.first_name} ${client.profile.last_name}` : client.email }}
                            <Badge v-if="client.status === 'pending'" variant="secondary">Pendiente</Badge>
                        </span>
                        <span class="text-sm text-gray-400">{{ client.email }}</span>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
