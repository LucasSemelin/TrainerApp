<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Button from './ui/button/Button.vue';

interface Props {
    clientId: string;
}

const props = defineProps<Props>();

const page = usePage();

const form = useForm({
    trainer_id: page.props.auth.user.id,
    client_id: props.clientId,
});

const createWorkout = () => {
    form.post('/workouts', {
        onSuccess: () => {
            // Usar router.visit para navegar directamente
            router.visit(`/clients/${props.clientId}/workouts`);
        },
        onError: (errors) => {
            console.error('Error creating workout:', errors);
        },
    });
};
</script>

<template>
    <Dialog>
        <DialogTrigger>
            <slot name="trigger">
                <Button
                    class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium whitespace-nowrap text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    Crear Rutina
                </Button>
            </slot>
        </DialogTrigger>

        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-lg leading-none font-semibold tracking-tight"> Nueva Rutina </DialogTitle>
                <p class="text-sm text-muted-foreground">¿Estás seguro de que quieres crear una nueva rutina para este alumno?</p>
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
                    :disabled="form.processing"
                    @click="createWorkout"
                    class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium whitespace-nowrap text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    <span v-if="form.processing" class="mr-2">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                    </span>
                    {{ form.processing ? 'Creando...' : 'Crear Rutina' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
