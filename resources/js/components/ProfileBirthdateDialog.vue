<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    open: boolean;
    profileId: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const dateOfBirth = ref('');
const processing = ref(false);

const handleSubmit = () => {
    if (!dateOfBirth.value) return;

    processing.value = true;

    router.patch(
        `/profiles/${props.profileId}/birthdate`,
        { date_of_birth: dateOfBirth.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
                dateOfBirth.value = '';
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const handleOpenChange = (value: boolean) => {
    if (!processing.value) {
        emit('update:open', value);
        if (!value) {
            dateOfBirth.value = '';
        }
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Fecha de nacimiento</DialogTitle>
                <DialogDescription>Selecciona la fecha de nacimiento del alumno</DialogDescription>
            </DialogHeader>

            <div class="py-4">
                <label class="mb-2 block text-sm font-medium text-foreground">Fecha de nacimiento</label>
                <input
                    v-model="dateOfBirth"
                    type="date"
                    class="w-full rounded-xl border border-border bg-card/50 px-4 py-3 text-foreground transition-all focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                    :max="new Date().toISOString().split('T')[0]"
                />
            </div>

            <DialogFooter>
                <Button variant="outline" @click="handleOpenChange(false)" :disabled="processing">Cancelar</Button>
                <Button @click="handleSubmit" :disabled="!dateOfBirth || processing">
                    {{ processing ? 'Guardando...' : 'Guardar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
