<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Home, MessageSquare, Settings, Users } from 'lucide-vue-next';
import { ref } from 'vue';

import { Form } from '@inertiajs/vue3';

import { handlePrompt } from '@/actions/App/Http/Controllers/AiController';

const emit = defineEmits(['ai-prompt', 'ai-prompt-parsed', 'ai-prompt-error']);
const showPrompt = ref(false);
const promptText = ref('');
const loading = ref(false);

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
            <nav class="flex items-center justify-between rounded-full bg-white/90 px-4 py-2 shadow-lg backdrop-blur-md dark:bg-slate-800/90">
                <Link href="/dashboard" aria-label="Home" class="rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <Home class="h-6 w-6 text-slate-700 dark:text-slate-200" />
                </Link>

                <Link href="/clients" aria-label="Clients" class="rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <Users class="h-6 w-6 text-slate-700 dark:text-slate-200" />
                </Link>

                <button type="button" @click="openPrompt" aria-label="AI Prompt" class="rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <MessageSquare class="h-6 w-6 text-slate-700 dark:text-slate-200" />
                </button>

                <Link href="/settings" aria-label="Settings" class="rounded-full p-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <Settings class="h-6 w-6 text-slate-700 dark:text-slate-200" />
                </Link>
            </nav>
        </div>
    </div>

    <!-- AI prompt modal -->
    <div v-if="showPrompt" class="fixed inset-0 z-50 flex items-center justify-center md:hidden">
        <div class="absolute inset-0 bg-black/50" @click="closePrompt"></div>
        <div class="relative mx-4 w-[90%] max-w-lg rounded-lg bg-white p-4 dark:bg-slate-800">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-lg font-medium text-slate-800 dark:text-slate-100">Prompt para IA</h3>
                <button @click="closePrompt" class="rounded p-1 text-slate-500 hover:bg-slate-100">✕</button>
            </div>
            <Form :action="handlePrompt()" method="post" :options="{ preserveUrl: true }" disableWhileProcessing class="inert:opacity-50">
                <textarea
                    rows="6"
                    v-model="promptText"
                    name="prompt"
                    class="w-full rounded-md border bg-white p-2 text-slate-900 dark:bg-slate-900 dark:text-slate-100"
                    placeholder="Escribe aquí tu prompt para la IA..."
                ></textarea>
                <div class="mt-3 flex justify-end gap-2">
                    <button @click="closePrompt" class="rounded bg-slate-100 px-3 py-2 dark:bg-slate-700">Cancelar</button>
                    <button type="submit" :disabled="!promptText" class="rounded bg-blue-600 px-3 py-2 text-white disabled:opacity-50">Enviar</button>
                </div>
            </Form>
        </div>
    </div>
</template>
