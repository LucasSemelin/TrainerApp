<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from './ui/button/Button.vue';

const emit = defineEmits(['created']);

const form = useForm({
    first_name: '',
    email: '',
    confirm_existing: false,
});

const submitting = ref(false);
const showConfirmation = ref(false);
const existingUserInfo = ref<{ email: string; name: string } | null>(null);

const submit = async () => {
    submitting.value = true;
    await form.post('/clients', {
        preserveScroll: true,
        onSuccess: () => {
            emit('created');
            form.reset();
            showConfirmation.value = false;
            existingUserInfo.value = null;
        },
        onError: (errors: any) => {
            // Check if error is for existing user
            if (errors.user_exists) {
                try {
                    const userData = JSON.parse(errors.user_exists);
                    showConfirmation.value = true;
                    existingUserInfo.value = userData;
                    submitting.value = false; // Reset submitting for confirmation
                    // Clear the error from form
                    form.clearErrors('user_exists');
                } catch (e) {
                    console.error('Error parsing user data:', e);
                    submitting.value = false;
                }
            }
        },
        onFinish: () => {
            if (!showConfirmation.value) {
                submitting.value = false;
            }
        },
    });
};

const confirmInvite = async () => {
    form.confirm_existing = true;
    await submit();
};

const cancelConfirmation = () => {
    showConfirmation.value = false;
    existingUserInfo.value = null;
    form.confirm_existing = false;
    submitting.value = false;
    form.clearErrors();
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
                <p class="text-sm text-muted-foreground">Agregá la información del nuevo alumno acá.</p>
            </DialogHeader>

            <!-- Confirmation message when user already exists -->
            <div v-if="showConfirmation" class="space-y-4">
                <div class="rounded-lg border border-primary/30 bg-primary/5 p-4">
                    <p class="mb-2 text-sm font-medium text-foreground">
                        El email <strong>{{ existingUserInfo?.email }}</strong> ya está registrado.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        ¿Querés invitar a <strong>{{ existingUserInfo?.name }}</strong> a ser tu alumno?
                    </p>
                </div>
                <DialogFooter class="gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancelConfirmation"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium whitespace-nowrap ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="button"
                        @click="confirmInvite"
                        :disabled="submitting"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium whitespace-nowrap text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Sí, invitar
                    </Button>
                </DialogFooter>
            </div>

            <!-- Regular form when no confirmation needed -->
            <form v-else @submit.prevent="submit" class="space-y-4">
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
                            placeholder="Ingresá el nombre"
                            required
                        />
                        <div v-if="form.errors.first_name" class="text-sm font-medium text-destructive">
                            {{ form.errors.first_name }}
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
