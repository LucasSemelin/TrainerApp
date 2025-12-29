<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, Home, Settings, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import { Form } from '@inertiajs/vue3';

import { handlePrompt } from '@/actions/App/Http/Controllers/AiController';

const emit = defineEmits(['ai-prompt', 'ai-prompt-parsed', 'ai-prompt-error']);
const showPrompt = ref(false);
const promptText = ref('');
const loading = ref(false);

const page = usePage();

// Número de notificaciones sin leer (esto debería venir del backend)
const notificationCount = ref(3);

// Función para determinar si un link está activo
const isActive = computed(() => (href: string) => {
    const currentPath = page.url;
    if (href === '/dashboard') {
        return currentPath === '/' || currentPath === '/dashboard';
    }
    return currentPath.startsWith(href);
});

function openPrompt() {
    showPrompt.value = true;
}

function closePrompt() {
    showPrompt.value = false;
    loading.value = false;
}

// CREAR EL ENVIO DE PROMMPT MANUAL....VER RESPUESTA EN CASO DE ERROR O SUCCESS
</script>

<template>
    <div class="fixed right-4 bottom-4 left-4 z-50 md:hidden">
        <div class="mx-auto max-w-lg">
            <nav
                class="flex items-center justify-between rounded-2xl border bg-background/95 px-6 py-3 shadow-lg backdrop-blur supports-[backdrop-filter]:bg-background/60"
            >
                <Link
                    href="/dashboard"
                    aria-label="Home"
                    :class="[
                        'inline-flex items-center justify-center rounded-lg p-2 text-sm font-medium ring-offset-background transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
                        isActive('/dashboard') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent hover:text-accent-foreground',
                    ]"
                >
                    <Home class="h-5 w-5" />
                </Link>

                <Link
                    href="/clients"
                    aria-label="Clients"
                    :class="[
                        'inline-flex items-center justify-center rounded-lg p-2 text-sm font-medium ring-offset-background transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
                        isActive('/clients') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent hover:text-accent-foreground',
                    ]"
                >
                    <Users class="h-5 w-5" />
                </Link>

                <Link
                    href="/notifications"
                    aria-label="Notifications"
                    :class="[
                        'relative inline-flex items-center justify-center rounded-lg p-2 text-sm font-medium ring-offset-background transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
                        isActive('/notifications') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent hover:text-accent-foreground',
                    ]"
                >
                    <Bell class="h-5 w-5" />
                    <span
                        v-if="false"
                        class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-[10px] font-semibold text-destructive-foreground"
                    >
                        {{ notificationCount > 99 ? '99+' : notificationCount }}
                    </span>
                </Link>

                <!-- <button
                    type="button"
                    @click="openPrompt"
                    aria-label="AI Prompt"
                    class="inline-flex items-center justify-center rounded-lg bg-primary p-2.5 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    <MessageSquare class="h-5 w-5" />
                </button> -->

                <Link
                    href="/settings"
                    aria-label="Settings"
                    :class="[
                        'inline-flex items-center justify-center rounded-lg p-2 text-sm font-medium ring-offset-background transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
                        isActive('/settings') ? 'bg-primary text-primary-foreground' : 'hover:bg-accent hover:text-accent-foreground',
                    ]"
                >
                    <Settings class="h-5 w-5" />
                </Link>
            </nav>
        </div>
    </div>

    <!-- AI prompt modal -->
    <div v-if="showPrompt" class="fixed inset-0 z-50 flex items-center justify-center md:hidden">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-sm" @click="closePrompt"></div>
        <div class="relative mx-4 w-[90%] max-w-lg rounded-lg border bg-background p-6 shadow-lg">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg leading-none font-semibold tracking-tight">Prompt para IA</h3>
                <button
                    @click="closePrompt"
                    class="inline-flex items-center justify-center rounded-md p-1 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    ✕
                </button>
            </div>
            <Form :action="handlePrompt()" method="post" :options="{ preserveUrl: true }" disableWhileProcessing class="inert:opacity-50">
                <div class="space-y-4">
                    <textarea
                        rows="6"
                        v-model="promptText"
                        name="prompt"
                        class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Escribe aquí tu prompt para la IA..."
                    ></textarea>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closePrompt"
                            class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="!promptText"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Enviar
                        </button>
                    </div>
                </div>
            </Form>
        </div>
    </div>
</template>
