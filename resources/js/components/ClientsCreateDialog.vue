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
    gender: '',
});

const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    await form.post('/clients', {
        onSuccess: () => {
            emit('created');
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
                <Button class="btn">Nuevo alumno</Button>
            </slot>
        </DialogTrigger>

        <DialogContent>
            <DialogHeader>
                <DialogTitle>Nuevo alumno</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit">
                <div class="grid gap-3">
                    <label>
                        <span>First name</span>
                        <input v-model="form.first_name" type="text" class="input" required />
                    </label>

                    <label>
                        <span>Last name</span>
                        <input v-model="form.last_name" type="text" class="input" required />
                    </label>

                    <label>
                        <span>Email</span>
                        <input v-model="form.email" type="email" class="input" required />
                    </label>

                    <label>
                        <span>Gender</span>
                        <select v-model="form.gender" class="input">
                            <option value="">Seleccionar</option>
                            <option value="male">Masculino</option>
                            <option value="female">Femenino</option>
                            <option value="other">Otro</option>
                        </select>
                    </label>
                </div>

                <DialogFooter>
                    <DialogClose>
                        <button type="button" class="btn-secondary">Cancel</button>
                    </DialogClose>
                    <button type="submit" :disabled="submitting" class="btn-primary">Create</button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
