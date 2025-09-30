<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from './ui/button/Button.vue';

const emit = defineEmits(['created']);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
});

const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    await form.post('/clients', {
        onSuccess: () => {
            emit('created');
            form.reset();
        },
        onFinish: () => {
            submitting.value = false;
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
                    Nuevo alumno
                </Button>
            </slot>
        </DialogTrigger>

        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-lg leading-none font-semibold tracking-tight"> Nuevo alumno </DialogTitle>
                <p class="text-sm text-muted-foreground">Agrega la información del nuevo alumno aquí.</p>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <label for="first_name" class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Nombre
                        </label>
                        <input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Ingresa el nombre"
                            required
                        />
                        <div v-if="form.errors.first_name" class="text-sm font-medium text-destructive">
                            {{ form.errors.first_name }}
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <label for="last_name" class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Apellido
                        </label>
                        <input
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Ingresa el apellido"
                            required
                        />
                        <div v-if="form.errors.last_name" class="text-sm font-medium text-destructive">
                            {{ form.errors.last_name }}
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <label for="email" class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="ejemplo@correo.com"
                            required
                        />
                        <div v-if="form.errors.email" class="text-sm font-medium text-destructive">
                            {{ form.errors.email }}
                        </div>
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
                        type="submit"
                        :disabled="submitting || form.processing"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium whitespace-nowrap text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        <span v-if="submitting || form.processing" class="mr-2">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </span>
                        {{ submitting || form.processing ? 'Creando...' : 'Crear alumno' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
