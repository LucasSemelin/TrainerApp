<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Dumbbell, Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Exercise {
    id: string;
    name: string;
    alternative_names?: string[];
    slug: string;
    description?: string;
    categories?: Record<string, string[]>;
}

interface Props {
    exercises: Exercise[];
    search: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ejercicios',
        href: '/exercises',
    },
];

const page = usePage();
const searchTerm = ref(props.search);
const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const exerciseToDelete = ref<Exercise | null>(null);

// Form for creating new exercise
const createForm = useForm({
    name: '',
    description: '',
});

// Handle search with Inertia
const handleSearch = () => {
    router.get(
        '/exercises',
        { search: searchTerm.value },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['exercises', 'search'],
        },
    );
};

// Debounce search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(handleSearch, 300);
};

// Create exercise
const createExercise = () => {
    createForm.post('/exercises', {
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
        },
    });
};

// Delete exercise
const confirmDelete = (exercise: Exercise) => {
    exerciseToDelete.value = exercise;
    showDeleteDialog.value = true;
};

const deleteExercise = () => {
    if (!exerciseToDelete.value) return;

    router.delete(`/exercises/${exerciseToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            exerciseToDelete.value = null;
        },
    });
};

// Get category badges for an exercise
const getCategoryBadges = (categories?: Record<string, string[]>) => {
    if (!categories) return [];

    const badges: { label: string; variant: string }[] = [];

    // Prioritize muscle_group
    if (categories.muscle_group && categories.muscle_group.length > 0) {
        categories.muscle_group.forEach((cat) => {
            badges.push({ label: cat, variant: 'default' });
        });
    }

    // Then movement_pattern if no muscle_group
    if (badges.length === 0 && categories.movement_pattern && categories.movement_pattern.length > 0) {
        categories.movement_pattern.forEach((cat) => {
            badges.push({ label: cat, variant: 'secondary' });
        });
    }

    // Add equipment as outline badges
    if (categories.equipment && categories.equipment.length > 0) {
        categories.equipment.slice(0, 2).forEach((cat) => {
            badges.push({ label: cat, variant: 'outline' });
        });
    }

    return badges.slice(0, 4); // Limit to 4 badges
};
</script>

<template>
    <Head title="Ejercicios" />
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6 pb-24">
            <!-- Header -->
            <div class="flex items-center justify-end">
                <Dialog v-model:open="showCreateDialog">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Nuevo ejercicio
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <form @submit.prevent="createExercise">
                            <DialogHeader>
                                <DialogTitle>Crear nuevo ejercicio</DialogTitle>
                                <DialogDescription>Agrega un nuevo ejercicio al catálogo</DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="name">Nombre del ejercicio</Label>
                                    <Input
                                        id="name"
                                        v-model="createForm.name"
                                        placeholder="Ej: Press de banca"
                                        :class="{ 'border-destructive': createForm.errors.name }"
                                        required
                                    />
                                    <span v-if="createForm.errors.name" class="text-sm text-destructive">
                                        {{ createForm.errors.name }}
                                    </span>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="description">Descripción (opcional)</Label>
                                    <textarea
                                        id="description"
                                        v-model="createForm.description"
                                        placeholder="Descripción del ejercicio..."
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button variant="outline" type="button">Cancelar</Button>
                                </DialogClose>
                                <Button type="submit" :disabled="createForm.processing">
                                    {{ createForm.processing ? 'Creando...' : 'Crear ejercicio' }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Search -->
            <div class="relative">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="searchTerm" @input="debouncedSearch" placeholder="Buscar ejercicios por nombre..." class="pl-10" />
            </div>

            <!-- Exercise count -->
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <span
                    >{{ props.exercises.length }} ejercicio{{ props.exercises.length !== 1 ? 's' : '' }} encontrado{{
                        props.exercises.length !== 1 ? 's' : ''
                    }}</span
                >
            </div>

            <!-- Exercises Grid -->
            <div v-if="props.exercises.length === 0" class="flex flex-1 flex-col items-center justify-center gap-6 py-12">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                    <Dumbbell class="h-10 w-10 text-muted-foreground" />
                </div>
                <div class="flex flex-col items-center gap-2 text-center">
                    <h2 class="text-xl font-semibold text-foreground">
                        {{ searchTerm ? 'No se encontraron ejercicios' : 'No hay ejercicios todavía' }}
                    </h2>
                    <p class="max-w-md text-sm text-muted-foreground">
                        {{
                            searchTerm
                                ? 'Intenta con otros términos de búsqueda'
                                : 'Comenzá agregando ejercicios para poder incluirlos en las rutinas de tus alumnos'
                        }}
                    </p>
                </div>
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="exercise in props.exercises"
                    :key="exercise.id"
                    class="group relative overflow-hidden transition-all hover:border-primary/50"
                >
                    <CardHeader>
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1">
                                <CardTitle>{{ exercise.name }}</CardTitle>
                            </div>
                            <Dialog v-model:open="showDeleteDialog">
                                <DialogTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 shrink-0"
                                        @click="confirmDelete(exercise)"
                                        aria-label="Eliminar ejercicio"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </DialogTrigger>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Alternative names -->
                        <div v-if="exercise.alternative_names && exercise.alternative_names.length > 1" class="mb-3">
                            <p class="mb-1 text-xs font-medium text-muted-foreground">Nombres alternativos:</p>
                            <p class="text-xs text-muted-foreground">
                                {{ exercise.alternative_names.filter((n) => n !== exercise.name).join(', ') }}
                            </p>
                        </div>

                        <!-- Categories -->
                        <div class="flex flex-wrap gap-1.5">
                            <Badge v-for="(badge, index) in getCategoryBadges(exercise.categories)" :key="index" :variant="badge.variant as any">
                                {{ badge.label }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Delete confirmation dialog -->
            <Dialog v-model:open="showDeleteDialog">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>¿Eliminar ejercicio?</DialogTitle>
                        <DialogDescription>
                            ¿Estás seguro de que querés eliminar "<strong>{{ exerciseToDelete?.name }}</strong
                            >"? Esta acción no se puede deshacer.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="outline" type="button">Cancelar</Button>
                        </DialogClose>
                        <Button variant="destructive" @click="deleteExercise">Eliminar</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppSidebarLayout>
</template>
