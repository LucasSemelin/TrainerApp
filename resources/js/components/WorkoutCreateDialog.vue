<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Button from './ui/button/Button.vue';
import ButtonSecondary from './ui/button/ButtonSecondary.vue';

interface Props {
    clientId: string;
}

const props = defineProps<Props>();

const page = usePage();

const form = useForm({
    name: '',
    trainer_id: page.props.auth.user.id,
    client_id: props.clientId,
});

const createWorkout = () => {
    if (!form.name.trim()) {
        form.setError('name', 'El nombre de la rutina es obligatorio');
        return;
    }

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
        <template v-if="$slots.trigger">
            <DialogTrigger>
                <ButtonSecondary
                    type="button"
                    variant="outline"
                    class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium whitespace-nowrap ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    Crear rutina
                </ButtonSecondary>
            </DialogTrigger>
        </template>

        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-lg leading-none font-semibold tracking-tight"> Nueva Rutina </DialogTitle>
                <p class="text-sm text-muted-foreground">Completa la información para crear una nueva rutina para este alumno.</p>
            </DialogHeader>

            <div class="grid gap-4 py-4">
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="name" class="text-right"> Nombre </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Ej: Rutina de fuerza"
                        class="col-span-3"
                        :class="{ 'border-red-500': form.errors.name }"
                    />
                </div>
                <div v-if="form.errors.name" class="col-span-4 text-right text-sm text-red-500">
                    {{ form.errors.name }}
                </div>
            </div>

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
                    variant="secondary"
                    :disabled="form.processing || !form.name.trim()"
                    @click="createWorkout"
                    class="inline-flex h-10 items-center justify-center rounded-md border border-primary/30 bg-transparent px-4 py-2 text-sm font-medium text-primary ring-offset-background transition-colors hover:border-primary/40 hover:bg-primary/5 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
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
