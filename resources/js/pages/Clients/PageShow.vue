<script setup lang="ts">
import ProfileBirthdateDialog from '@/components/ProfileBirthdateDialog.vue';
import ProfileSexDialog from '@/components/ProfileSexDialog.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { User } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { ArchiveRestore, Dumbbell, Mail, MessageCircle, User as UserIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const client = computed<User>(() => page.props.client as User);
const sexDialogOpen = ref(false);
const birthdateDialogOpen = ref(false);

const getGenderLabel = (gender: string) => {
    const labels: Record<string, string> = {
        male: 'Masculino',
        female: 'Femenino',
        other: 'Otro',
    };
    return labels[gender] || gender;
};

const getAge = (dateOfBirth: string) => {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    return `${age} años`;
};

const navigateToWorkout = (workoutId: string) => {
    router.visit(`/clients/${client.value.id}/workouts/${workoutId}`);
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        active: 'Activa',
        draft: 'Borrador',
        archived: 'Archivada',
    };
    return labels[status] || status;
};

const getStatusVariant = (status: string) => {
    const variants: Record<string, string> = {
        active: 'border-primary bg-primary/10 text-primary',
        draft: 'border-muted-foreground/30 bg-muted/30 text-muted-foreground',
        archived: 'border-yellow-500/30 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    };
    return variants[status] || variants.draft;
};

const unarchiveWorkout = (workoutId: string, event: Event) => {
    event.stopPropagation();
    router.patch(`/clients/${client.value.id}/workouts/${workoutId}/unarchive`);
};
</script>

<template>
    <AppLayout title="Detalles del alumno">
        <div class="space-y-6 px-6 pt-6">
            <!-- Header con información básica -->
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <UserIcon class="size-10" />
                    </div>
                    <div class="flex-1 space-y-3">
                        <h1 class="text-2xl font-semibold text-foreground">{{ client.profile?.first_name }} {{ client.profile?.last_name }}</h1>

                        <!-- Botones de contacto -->
                        <div class="flex gap-3">
                            <Button variant="outline" class="flex items-center gap-2">
                                <MessageCircle class="size-4" />
                                WhatsApp
                            </Button>
                            <Button variant="outline" class="flex items-center gap-2">
                                <Mail class="size-4" />
                                Email
                            </Button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="sexDialogOpen = true"
                        :class="[
                            'inline-flex rounded-full px-4 py-2 text-xs font-medium transition-all',
                            client.profile?.gender
                                ? 'border border-primary bg-primary/10 text-primary hover:bg-primary/20'
                                : 'border border-dashed border-border bg-muted/30 text-muted-foreground hover:border-primary/50 hover:text-primary',
                        ]"
                    >
                        {{ client.profile?.gender ? getGenderLabel(client.profile.gender) : 'Sexo' }}
                    </button>
                    <button
                        @click="birthdateDialogOpen = true"
                        :class="[
                            'inline-flex rounded-full px-4 py-2 text-xs font-medium transition-all',
                            client.profile?.date_of_birth
                                ? 'border border-primary bg-primary/10 text-primary hover:bg-primary/20'
                                : 'border border-dashed border-border bg-muted/30 text-muted-foreground hover:border-primary/50 hover:text-primary',
                        ]"
                    >
                        {{ client.profile?.date_of_birth ? getAge(client.profile.date_of_birth) : 'Edad' }}
                    </button>
                </div>
            </div>

            <ProfileSexDialog v-model:open="sexDialogOpen" :profile-id="client.profile?.id" />
            <ProfileBirthdateDialog v-model:open="birthdateDialogOpen" :profile-id="client.profile?.id" />

            <!-- Sección de rutinas -->
            <div class="space-y-3">
                <h2 class="text-lg font-semibold text-foreground">Rutinas</h2>
                <div v-if="client.my_workouts && client.my_workouts.length > 0" class="space-y-2">
                    <button
                        v-for="workout in client.my_workouts"
                        :key="workout.id"
                        @click="navigateToWorkout(workout.id)"
                        class="group flex w-full items-center gap-3 rounded-lg border border-border bg-card p-4 text-left transition-all hover:border-primary hover:bg-primary/5"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/20"
                        >
                            <Dumbbell class="size-5" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-foreground group-hover:text-primary">{{ workout.name }}</h3>
                            <p class="text-sm text-muted-foreground">Creada el {{ new Date(workout.created_at).toLocaleDateString('es-ES') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="['rounded-full border px-3 py-1 text-xs font-medium', getStatusVariant(workout.status)]">
                                {{ getStatusLabel(workout.status) }}
                            </span>
                            <button
                                v-if="workout.status === 'archived'"
                                @click="(e) => unarchiveWorkout(workout.id, e)"
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-yellow-500/30 bg-yellow-500/10 text-yellow-600 transition-colors hover:bg-yellow-500/20 dark:text-yellow-400"
                                title="Desarchivar rutina"
                            >
                                <ArchiveRestore class="size-4" />
                            </button>
                        </div>
                    </button>
                </div>
                <div v-else class="rounded-lg border border-dashed border-border bg-muted/30 p-8 text-center">
                    <Dumbbell class="mx-auto mb-2 size-8 text-muted-foreground/50" />
                    <p class="text-sm text-muted-foreground">Este cliente aún no tiene rutinas asignadas</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
