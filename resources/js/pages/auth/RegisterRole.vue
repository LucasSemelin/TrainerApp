<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { storeRole } from '@/routes/user';
import { Head, router } from '@inertiajs/vue3';
import { Check, Dumbbell, Users } from 'lucide-vue-next';
import { ref } from 'vue';

const selectedRole = ref<'trainer' | 'client' | null>(null);
const processing = ref(false);

const selectRole = (role: 'trainer' | 'client') => {
    selectedRole.value = role;
};

const handleSubmit = () => {
    if (!selectedRole.value) return;

    processing.value = true;
    router.post(
        storeRole().url,
        { role: selectedRole.value },
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Seleccioná tu rol" />
    <AuthLayout>
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-8">
            <div class="flex flex-col gap-2 text-center">
                <h1 class="text-3xl font-bold tracking-tight">Hola 👋🏼</h1>
                <p class="text-muted-foreground">Seleccioná tu rol para comenzar</p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:gap-6">
                <!-- Tarjeta Entrenador -->
                <button
                    type="button"
                    @click="selectRole('trainer')"
                    :class="[
                        'group relative flex flex-col items-center gap-4 rounded-xl border-2 p-4 transition-all duration-200 md:p-8',
                        selectedRole === 'trainer'
                            ? 'border-primary bg-primary/5 shadow-lg'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-accent/50',
                    ]"
                >
                    <div
                        v-if="selectedRole === 'trainer'"
                        class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground md:top-4 md:right-4 md:h-6 md:w-6"
                    >
                        <Check class="h-3 w-3 md:h-4 md:w-4" />
                    </div>

                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors md:h-20 md:w-20">
                        <Dumbbell class="h-8 w-8 md:h-10 md:w-10" />
                    </div>

                    <div class="flex flex-col gap-2 text-center">
                        <h2 class="text-lg font-semibold md:text-2xl">Entrenador</h2>
                        <p class="text-xs text-muted-foreground md:text-sm">Creá rutinas y gestioná tus alumnos</p>
                    </div>
                </button>

                <!-- Tarjeta Alumno -->
                <button
                    type="button"
                    @click="selectRole('client')"
                    :class="[
                        'group relative flex flex-col items-center gap-4 rounded-xl border-2 p-4 transition-all duration-200 md:p-8',
                        selectedRole === 'client'
                            ? 'border-primary bg-primary/5 shadow-lg'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-accent/50',
                    ]"
                >
                    <div
                        v-if="selectedRole === 'client'"
                        class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground md:top-4 md:right-4 md:h-6 md:w-6"
                    >
                        <Check class="h-3 w-3 md:h-4 md:w-4" />
                    </div>

                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors md:h-20 md:w-20">
                        <Users class="h-8 w-8 md:h-10 md:w-10" />
                    </div>

                    <div class="flex flex-col gap-2 text-center">
                        <h2 class="text-lg font-semibold md:text-2xl">Alumno</h2>
                        <p class="text-xs text-muted-foreground md:text-sm">Seguí tus rutinas y progreso</p>
                    </div>
                </button>
            </div>

            <Button @click="handleSubmit" :disabled="!selectedRole || processing" size="lg" class="w-full">
                {{ processing ? 'Guardando...' : 'Continuá' }}
            </Button>
        </div>
    </AuthLayout>
</template>
