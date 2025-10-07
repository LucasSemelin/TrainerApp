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
                            v-model.number="form.weight"
                            type="number"
                            step="0.1"
                            min="0"
                            placeholder="20.5"
                            :class="{ 'border-red-500': errors.weight }"
                        />
                        <p v-if="errors.weight" class="text-sm text-red-500">
                            {{ errors.weight }}
                        </p>
                    </div>

                    <!-- Tiempo de Descanso -->
                    <div class="space-y-2">
                        <Label for="rest_time_seconds">Descanso (en seg)</Label>
                        <Input
                            id="rest_time_seconds"
                            v-model.number="form.rest_time_seconds"
                            type="number"
                            min="0"
                            placeholder="90"
                            :class="{ 'border-red-500': errors.rest_time_seconds }"
                        />
                        <p v-if="errors.rest_time_seconds" class="text-sm text-red-500">
                            {{ errors.rest_time_seconds }}
                        </p>
                    </div>
                </div>

                <!-- Segunda fila: Repeticiones -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Repeticiones Mínimas -->
                    <div class="space-y-2">
                        <Label for="min_reps">Rep. Mínimas *</Label>
                        <Input
                            id="min_reps"
                            v-model.number="form.min_reps"
                            type="number"
                            min="1"
                            placeholder="8"
                            required
                            :class="{ 'border-red-500': errors.min_reps }"
                        />
                        <p v-if="errors.min_reps" class="text-sm text-red-500">
                            {{ errors.min_reps }}
                        </p>
                    </div>

                    <!-- Repeticiones Máximas -->
                    <div class="space-y-2">
                        <Label for="max_reps">Rep. Máximas</Label>
                        <Input
                            id="max_reps"
                            v-model.number="form.max_reps"
                            type="number"
                            :min="form.min_reps || 1"
                            placeholder="12"
                            :class="{ 'border-red-500': errors.max_reps }"
                        />
                        <p v-if="errors.max_reps" class="text-sm text-red-500">
                            {{ errors.max_reps }}
                        </p>
                    </div>
                </div>

                <!-- Notas -->
                <div class="space-y-2">
                    <Label for="notes">Notas (opcional)</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        class="flex min-h-[60px] w-full resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        maxlength="1000"
                        placeholder="Notas adicionales sobre la serie..."
                        :class="{ 'border-red-500': errors.notes }"
                    />
                    <p v-if="errors.notes" class="text-sm text-red-500">
                        {{ errors.notes }}
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
    workoutExerciseId: string;
    existingSets?: any[];
}

interface ExerciseSetForm {
    weight: number | undefined;
    min_reps: number | undefined;
    max_reps: number | undefined;
    rest_time_seconds: number | undefined;
    notes: string;
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
    weight: undefined,
    min_reps: undefined,
    max_reps: undefined,
    rest_time_seconds: undefined,
    notes: '',
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
    form.weight = undefined;
    form.min_reps = undefined;
    form.max_reps = undefined;
    form.rest_time_seconds = undefined;
    form.notes = '';
    errors.value = {};
};

const closeDialog = () => {
    emit('update:open', false);
    resetForm();
};

const validateForm = (): boolean => {
    errors.value = {};

    if (form.weight !== undefined && form.weight < 0) {
        errors.value.weight = 'El peso debe ser mayor o igual a 0';
    }

    if (!form.min_reps || form.min_reps < 1) {
        errors.value.min_reps = 'Las repeticiones mínimas son requeridas y deben ser mayor a 0';
    }

    if (form.max_reps && form.min_reps && form.max_reps < form.min_reps) {
        errors.value.max_reps = 'Las repeticiones máximas deben ser mayor o igual a las mínimas';
    }

    if (form.rest_time_seconds && form.rest_time_seconds < 0) {
        errors.value.rest_time_seconds = 'El tiempo de descanso debe ser mayor o igual a 0';
    }

    if (form.notes && form.notes.length > 1000) {
        errors.value.notes = 'Las notas no pueden exceder 1000 caracteres';
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

        const response = await fetch(`/exercise-workouts/${props.workoutExerciseId}/sets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                weight: form.weight || null,
                min_reps: form.min_reps,
                max_reps: form.max_reps || form.min_reps,
                rest_time_seconds: form.rest_time_seconds || null,
                notes: form.notes || null,
            }),
        });

        // Verificar si la respuesta es JSON válido
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

        // Emitir evento con la nueva serie creada
        emit('created', data.set);

        // Cerrar el diálogo y resetear el formulario
        closeDialog();

        // Mostrar mensaje de éxito (puedes usar un toast aquí)
        console.log('Serie creada exitosamente:', data.message);
    } catch (error) {
        console.error('Error al crear la serie:', error);
        errors.value.general = error instanceof Error ? error.message : 'Error inesperado al crear la serie';
    } finally {
        loading.value = false;
    }
};
</script>
