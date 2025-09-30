<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from './ui/button/Button.vue';

interface Props {
    clientId: string;
    clientName: string;
}

const props = defineProps<Props>();

const submitting = ref(false);

const removeClient = async () => {
    submitting.value = true;

    try {
        router.delete(`/clients/${props.clientId}`, {
            onSuccess: () => {
                // La página se recargará automáticamente con Inertia
            },
            onError: (errors) => {
                console.error('Error removing client:', errors);
            },
            onFinish: () => {
                submitting.value = false;
            },
        });
    } catch (error) {
        console.error('Error removing client:', error);
        submitting.value = false;
    }
};
</script>

<template>
    <Dialog>
        <DialogTrigger>
            <slot name="trigger">
                <!-- El slot será reemplazado por el botón del trash -->
            </slot>
        </DialogTrigger>

        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-lg leading-none font-semibold tracking-tight"> Eliminar Alumno </DialogTitle>
                <p class="text-sm text-muted-foreground">
                    ¿Estás seguro de que quieres eliminar a <strong>{{ clientName }}</strong> de tu lista de alumnos? Esta acción solo lo desvinculará
                    de tu lista, el usuario no será eliminado del sistema.
                </p>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose>
                    <Button
                        type="button"
                        variant="outline"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium whitespace-nowrap ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Cancelar
                    </Button>
                </DialogClose>
                <Button
                    type="button"
                    :disabled="submitting"
                    @click="removeClient"
                    variant="destructive"
                    class="inline-flex h-10 items-center justify-center rounded-md bg-destructive px-4 py-2 text-sm font-medium whitespace-nowrap text-destructive-foreground ring-offset-background transition-colors hover:bg-destructive/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    <span v-if="submitting" class="mr-2">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                    </span>
                    {{ submitting ? 'Eliminando...' : 'Eliminar Alumno' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
