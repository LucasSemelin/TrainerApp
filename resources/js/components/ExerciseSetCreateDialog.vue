<template>
    <Dialog :open="open" @update:open="(value: boolean) => emit('update:open', value)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Crear Nueva Serie</DialogTitle>
                <DialogDescription> Agrega una nueva serie al ejercicio. Completa la información requerida. </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Primera fila: Peso y Tiempo de Descanso -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Peso -->
                    <div class="space-y-2">
                        <Label for="weight">Peso (kg)</Label>
                        <Input
                            id="weight"
                            v-model.number="form.target_weight"
                            type="number"
                            step="0.1"
                            min="0"
                            placeholder="20.5"
                            :class="{ 'border-red-500': errors.target_weight }"
                        />
                        <p v-if="errors.target_weight" class="text-sm text-red-500">
                            {{ errors.target_weight }}
                        </p>
                    </div>

                    <!-- Tiempo de Descanso -->
                    <div class="space-y-2">
                        <Label for="rest_seconds">Descanso (en seg)</Label>
                        <Input
                            id="rest_seconds"
                            v-model.number="form.rest_seconds"
                            type="number"
                            min="0"
                            placeholder="90"
                            :class="{ 'border-red-500': errors.rest_seconds }"
                        />
                        <p v-if="errors.rest_seconds" class="text-sm text-red-500">
                            {{ errors.rest_seconds }}
                        </p>
                    </div>
                </div>

                <!-- Segunda fila: Repeticiones y RPE -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Repeticiones -->
                    <div class="space-y-2">
                        <Label for="target_reps">Repeticiones</Label>
                        <Input
                            id="target_reps"
                            v-model.number="form.target_reps"
                            type="number"
                            min="1"
                            placeholder="12"
                            :class="{ 'border-red-500': errors.target_reps }"
                        />
                        <p v-if="errors.target_reps" class="text-sm text-red-500">
                            {{ errors.target_reps }}
                        </p>
                    </div>

                    <!-- RPE -->
                    <div class="space-y-2">
                        <Label for="target_rpe">RPE (1-10)</Label>
                        <Input
                            id="target_rpe"
                            v-model.number="form.target_rpe"
                            type="number"
                            min="1"
                            max="10"
                            step="0.5"
                            placeholder="8"
                            :class="{ 'border-red-500': errors.target_rpe }"
                        />
                        <p v-if="errors.target_rpe" class="text-sm text-red-500">
                            {{ errors.target_rpe }}
                        </p>
                    </div>
                </div>

                <!-- Tempo -->
                <div class="space-y-2">
                    <Label for="tempo">Tempo (e.g., 3-1-2-0)</Label>
                    <Input
                        id="tempo"
                        v-model="form.tempo"
                        type="text"
                        placeholder="3-1-2-0"
                        maxlength="20"
                        :class="{ 'border-red-500': errors.tempo }"
                    />
                    <p class="text-xs text-muted-foreground">Formato: excéntrico-pausa-concéntrico-pausa</p>
                    <p v-if="errors.tempo" class="text-sm text-red-500">
                        {{ errors.tempo }}
                    </p>
                </div>

                <!-- Error general -->
                <div
                    v-if="errors.general"
                    class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-500 dark:border-red-800 dark:bg-red-900/20"
                >
                    {{ errors.general }}
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeDialog" :disabled="loading"> Cancelar </Button>
                    <Button type="submit" :disabled="loading">
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        {{ loading ? 'Creando...' : 'Crear Serie' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2 } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

interface Props {
    open: boolean;
    sessionExerciseId: string;
    existingSets?: any[];
}

interface ExerciseSetForm {
    target_reps: number | undefined;
    target_weight: number | undefined;
    target_rpe: number | undefined;
    rest_seconds: number | undefined;
    tempo: string;
}

const props = withDefaults(defineProps<Props>(), {
    existingSets: () => [],
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    created: [set: any];
}>();

const loading = ref(false);
const errors = ref<Record<string, string>>({});

const form = reactive<ExerciseSetForm>({
    target_reps: undefined,
    target_weight: undefined,
    target_rpe: undefined,
    rest_seconds: undefined,
    tempo: '',
});

// Watch for open prop changes to reset form
watch(
    () => props.open,
    (newOpen: boolean) => {
        if (newOpen) {
            resetForm();
        }
    },
);

const resetForm = () => {
    form.target_reps = undefined;
    form.target_weight = undefined;
    form.target_rpe = undefined;
    form.rest_seconds = undefined;
    form.tempo = '';
    errors.value = {};
};

const closeDialog = () => {
    emit('update:open', false);
    resetForm();
};

const validateForm = (): boolean => {
    errors.value = {};

    if (form.target_weight !== undefined && form.target_weight < 0) {
        errors.value.target_weight = 'El peso debe ser mayor o igual a 0';
    }

    if (form.target_reps !== undefined && form.target_reps < 1) {
        errors.value.target_reps = 'Las repeticiones deben ser mayor a 0';
    }

    if (form.target_rpe !== undefined && (form.target_rpe < 1 || form.target_rpe > 10)) {
        errors.value.target_rpe = 'El RPE debe estar entre 1 y 10';
    }

    if (form.rest_seconds !== undefined && form.rest_seconds < 0) {
        errors.value.rest_seconds = 'El tiempo de descanso debe ser mayor o igual a 0';
    }

    return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    loading.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!csrfToken) {
            throw new Error('Token CSRF no encontrado. Recarga la página e intenta de nuevo.');
        }

        const response = await fetch(`/workout-session-exercises/${props.sessionExerciseId}/sets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                target_reps: form.target_reps || null,
                target_weight: form.target_weight || null,
                target_rpe: form.target_rpe || null,
                rest_seconds: form.rest_seconds || null,
                tempo: form.tempo || null,
            }),
        });

        let data;
        try {
            data = await response.json();
        } catch {
            if (response.status === 419) {
                throw new Error('Sesión expirada. Recarga la página e intenta de nuevo.');
            }
            throw new Error(`Error de servidor (${response.status}). Intenta de nuevo.`);
        }

        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('Token CSRF inválido. Recarga la página e intenta de nuevo.');
            }

            if (data.errors) {
                errors.value = data.errors;
            } else {
                throw new Error(data.message || `Error ${response.status}: ${response.statusText}`);
            }
            return;
        }

        emit('created', data.set);
        closeDialog();
    } catch (error) {
        console.error('Error al crear la serie:', error);
        errors.value.general = error instanceof Error ? error.message : 'Error inesperado al crear la serie';
    } finally {
        loading.value = false;
    }
};
</script>
