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

const selectedGender = ref<'male' | 'female' | 'other' | null>(null);
const processing = ref(false);

const genderOptions = [
    { value: 'male', label: 'Masculino' },
    { value: 'female', label: 'Femenino' },
    { value: 'other', label: 'Otro' },
];

const handleSubmit = () => {
    if (!selectedGender.value) return;

    processing.value = true;

    router.patch(
        `/profiles/${props.profileId}/gender`,
        { gender: selectedGender.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
                selectedGender.value = null;
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
            selectedGender.value = null;
        }
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Definir sexo</DialogTitle>
                <DialogDescription>Selecciona el sexo del alumno</DialogDescription>
            </DialogHeader>

            <div class="space-y-2 py-4">
                <button
                    v-for="option in genderOptions"
                    :key="option.value"
                    type="button"
                    @click="selectedGender = option.value as 'male' | 'female' | 'other'"
                    :class="[
                        'w-full rounded-xl border px-4 py-3 text-left transition-all',
                        selectedGender === option.value
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-border bg-card/50 text-foreground hover:border-primary/50',
                    ]"
                >
                    {{ option.label }}
                </button>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="handleOpenChange(false)" :disabled="processing">Cancelar</Button>
                <Button @click="handleSubmit" :disabled="!selectedGender || processing">
                    {{ processing ? 'Guardando...' : 'Guardar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
