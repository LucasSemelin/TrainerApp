<script setup lang="ts">
import ProfileBirthdateDialog from '@/components/ProfileBirthdateDialog.vue';
import ProfileSexDialog from '@/components/ProfileSexDialog.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Mail, MessageCircle, User as UserIcon } from 'lucide-vue-next';
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
        </div>
    </AppLayout>
</template>
