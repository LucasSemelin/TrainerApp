<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Dumbbell, Image as ImageIcon, ListOrdered, Pencil, Plus, Tag, Trash2, Wrench, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface CategoryOption {
    id: number;
    slug: string;
    label: string;
}

interface EquipmentOption {
    id: number;
    code: string;
    label: string;
}

interface TagOption {
    id: number;
    code: string;
    label: string;
}

interface MediaTypeOption {
    id: string;
    code: string;
    label: string;
}

interface Exercise {
    id: number;
    name: string;
    alternative_names: string[];
    slug: string;
    description: string | null;
    image_path: string | null;
    categories: Record<string, string[]>;
    equipment: string[];
    tags: string[];
    media: Array<{
        id: number;
        url: string;
        type: string;
        order: number;
    }>;
    instructions: Array<{
        set_name: string;
        order: number;
        instructions: Array<{
            step: number;
            text: string;
        }>;
    }>;
    currentEquipmentIds: number[];
    currentTagIds: number[];
}

interface CurrentInstructions {
    setup: string | null;
    execution_steps: string[];
    common_mistakes: string | null;
    cues: string | null;
    breathing: string | null;
}

const props = defineProps<{
    exercise: Exercise;
    availableCategories: {
        muscle_group: CategoryOption[];
        movement_pattern: CategoryOption[];
        difficulty: CategoryOption[];
    };
    availableEquipment: EquipmentOption[];
    availableTags: TagOption[];
    availableMediaTypes: MediaTypeOption[];
    currentInstructions: CurrentInstructions | null;
}>();

const breadcrumbs = [
    { label: 'Ejercicios', href: '/exercises' },
    { label: props.exercise.name, href: '#' },
];

// Modal states
const showMuscleGroupDialog = ref(false);
const showMovementPatternDialog = ref(false);
const showDifficultyDialog = ref(false);
const showEditDialog = ref(false);
const showEquipmentDialog = ref(false);
const showTagsDialog = ref(false);
const showMediaDialog = ref(false);
const showInstructionsDialog = ref(false);

// Form for categories
const categoryForm = useForm({
    type: '',
    category_ids: [] as number[],
});

// Form for exercise editing
const updateForm = useForm({
    name: props.exercise.name,
    description: props.exercise.description || '',
    image_path: props.exercise.image_path || '',
    alternative_names: [...(props.exercise.alternative_names || [])],
});

// Form for equipment
const equipmentForm = useForm({
    equipment_ids: [...(props.exercise.currentEquipmentIds || [])],
});

// Form for tags
const tagsForm = useForm({
    tag_ids: [...(props.exercise.currentTagIds || [])],
});

// Form for media
const mediaForm = useForm({
    url: '',
    media_type_id: '',
    provider: '' as string,
    is_primary: false,
});

// Form for instructions
const instructionsForm = useForm({
    setup: props.currentInstructions?.setup || '',
    execution_steps: [...(props.currentInstructions?.execution_steps || [])],
    common_mistakes: props.currentInstructions?.common_mistakes || '',
    cues: props.currentInstructions?.cues || '',
    breathing: props.currentInstructions?.breathing || '',
});

// Get current category IDs for a type
const getCurrentCategoryIds = (type: string): number[] => {
    const labels = props.exercise.categories[type] || [];
    const categories = props.availableCategories[type as keyof typeof props.availableCategories] || [];
    return categories.filter((cat) => labels.includes(cat.label)).map((cat) => cat.id);
};

// Open muscle group dialog
const openMuscleGroupDialog = () => {
    categoryForm.type = 'muscle_group';
    categoryForm.category_ids = getCurrentCategoryIds('muscle_group');
    showMuscleGroupDialog.value = true;
};

// Open movement pattern dialog
const openMovementPatternDialog = () => {
    categoryForm.type = 'movement_pattern';
    categoryForm.category_ids = getCurrentCategoryIds('movement_pattern');
    showMovementPatternDialog.value = true;
};

// Open difficulty dialog
const openDifficultyDialog = () => {
    categoryForm.type = 'difficulty';
    categoryForm.category_ids = getCurrentCategoryIds('difficulty');
    showDifficultyDialog.value = true;
};

// Toggle category selection
const toggleCategory = (categoryId: number) => {
    const index = categoryForm.category_ids.indexOf(categoryId);
    if (index > -1) {
        categoryForm.category_ids.splice(index, 1);
    } else {
        categoryForm.category_ids.push(categoryId);
    }
};

// Save categories
const saveCategories = () => {
    categoryForm.patch(`/exercises/${props.exercise.id}/categories`, {
        preserveScroll: true,
        onSuccess: () => {
            showMuscleGroupDialog.value = false;
            showMovementPatternDialog.value = false;
            showDifficultyDialog.value = false;
            categoryForm.reset();
        },
    });
};

// Alternative names management
const newAlternativeName = ref('');

const addAlternativeName = () => {
    if (newAlternativeName.value.trim() && !updateForm.alternative_names.includes(newAlternativeName.value.trim())) {
        updateForm.alternative_names.push(newAlternativeName.value.trim());
        newAlternativeName.value = '';
    }
};

const removeAlternativeName = (index: number) => {
    updateForm.alternative_names.splice(index, 1);
};

// Save exercise update
const saveExercise = () => {
    updateForm.put(`/exercises/${props.exercise.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditDialog.value = false;
        },
    });
};

// Equipment functions
const openEquipmentDialog = () => {
    equipmentForm.equipment_ids = [...(props.exercise.currentEquipmentIds || [])];
    showEquipmentDialog.value = true;
};

const toggleEquipment = (equipmentId: number) => {
    const index = equipmentForm.equipment_ids.indexOf(equipmentId);
    if (index > -1) {
        equipmentForm.equipment_ids.splice(index, 1);
    } else {
        equipmentForm.equipment_ids.push(equipmentId);
    }
};

const saveEquipment = () => {
    equipmentForm.patch(`/exercises/${props.exercise.id}/equipment`, {
        preserveScroll: true,
        onSuccess: () => {
            showEquipmentDialog.value = false;
        },
    });
};

// Tags functions
const openTagsDialog = () => {
    tagsForm.tag_ids = [...(props.exercise.currentTagIds || [])];
    showTagsDialog.value = true;
};

const toggleTag = (tagId: number) => {
    const index = tagsForm.tag_ids.indexOf(tagId);
    if (index > -1) {
        tagsForm.tag_ids.splice(index, 1);
    } else {
        tagsForm.tag_ids.push(tagId);
    }
};

const saveTags = () => {
    tagsForm.patch(`/exercises/${props.exercise.id}/tags`, {
        preserveScroll: true,
        onSuccess: () => {
            showTagsDialog.value = false;
        },
    });
};

// Media functions
const openMediaDialog = () => {
    mediaForm.reset();
    showMediaDialog.value = true;
};

const saveMedia = () => {
    mediaForm.post(`/exercises/${props.exercise.id}/media`, {
        preserveScroll: true,
        onSuccess: () => {
            showMediaDialog.value = false;
            mediaForm.reset();
        },
    });
};

const deleteMedia = (mediaId: number) => {
    if (confirm('¿Estás seguro de que querés eliminar este medio?')) {
        useForm({}).delete(`/exercises/${props.exercise.id}/media/${mediaId}`, {
            preserveScroll: true,
        });
    }
};

// Instructions functions
const openInstructionsDialog = () => {
    instructionsForm.setup = props.currentInstructions?.setup || '';
    instructionsForm.execution_steps = [...(props.currentInstructions?.execution_steps || [])];
    instructionsForm.common_mistakes = props.currentInstructions?.common_mistakes || '';
    instructionsForm.cues = props.currentInstructions?.cues || '';
    instructionsForm.breathing = props.currentInstructions?.breathing || '';
    showInstructionsDialog.value = true;
};

const newExecutionStep = ref('');

const addExecutionStep = () => {
    if (newExecutionStep.value.trim()) {
        instructionsForm.execution_steps.push(newExecutionStep.value.trim());
        newExecutionStep.value = '';
    }
};

const removeExecutionStep = (index: number) => {
    instructionsForm.execution_steps.splice(index, 1);
};

const saveInstructions = () => {
    instructionsForm.put(`/exercises/${props.exercise.id}/instructions`, {
        preserveScroll: true,
        onSuccess: () => {
            showInstructionsDialog.value = false;
        },
    });
};

// Watch for updates to reset forms
watch(
    () => props.exercise.currentEquipmentIds,
    (newVal) => {
        equipmentForm.equipment_ids = [...(newVal || [])];
    },
);

watch(
    () => props.exercise.currentTagIds,
    (newVal) => {
        tagsForm.tag_ids = [...(newVal || [])];
    },
);

watch(
    () => props.currentInstructions,
    (newVal) => {
        if (newVal) {
            instructionsForm.setup = newVal.setup || '';
            instructionsForm.execution_steps = [...(newVal.execution_steps || [])];
            instructionsForm.common_mistakes = newVal.common_mistakes || '';
            instructionsForm.cues = newVal.cues || '';
            instructionsForm.breathing = newVal.breathing || '';
        }
    },
);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="container max-w-5xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary/10">
                            <Dumbbell class="h-8 w-8 text-primary" />
                        </div>
                    </div>
                    <div class="flex flex-1 items-start justify-between gap-4">
                        <div class="flex-1 space-y-2">
                            <h1 class="text-2xl font-bold tracking-tight">{{ exercise.name }}</h1>
                            <div v-if="exercise.alternative_names.length > 0" class="flex flex-wrap gap-1 text-sm text-muted-foreground">
                                <span>También conocido como:</span>
                                <span class="italic">{{ exercise.alternative_names.join(', ') }}</span>
                            </div>
                        </div>
                        <Button variant="outline" size="icon" @click="showEditDialog = true">
                            <Pencil class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Categories Badges -->
                <div class="flex flex-wrap gap-2">
                    <!-- Muscle Groups -->
                    <template v-if="exercise.categories.muscle_group && exercise.categories.muscle_group.length > 0">
                        <Badge v-for="cat in exercise.categories.muscle_group" :key="cat" variant="default" class="cursor-pointer" @click="openMuscleGroupDialog">
                            {{ cat }}
                        </Badge>
                    </template>
                    <Badge v-else variant="secondary" class="cursor-pointer hover:bg-secondary/80" @click="openMuscleGroupDialog">
                        + Grupo muscular
                    </Badge>

                    <!-- Movement Patterns -->
                    <template v-if="exercise.categories.movement_pattern && exercise.categories.movement_pattern.length > 0">
                        <Badge v-for="cat in exercise.categories.movement_pattern" :key="cat" variant="secondary" class="cursor-pointer" @click="openMovementPatternDialog">
                            {{ cat }}
                        </Badge>
                    </template>
                    <Badge v-else variant="secondary" class="cursor-pointer hover:bg-secondary/80" @click="openMovementPatternDialog">
                        + Patrón de movimiento
                    </Badge>

                    <!-- Difficulty -->
                    <template v-if="exercise.categories.difficulty && exercise.categories.difficulty.length > 0">
                        <Badge v-for="cat in exercise.categories.difficulty" :key="cat" variant="outline" class="cursor-pointer" @click="openDifficultyDialog">
                            {{ cat }}
                        </Badge>
                    </template>
                    <Badge v-else variant="secondary" class="cursor-pointer hover:bg-secondary/80" @click="openDifficultyDialog">
                        + Dificultad
                    </Badge>
                </div>
            </div>

            <Separator />

            <!-- Main Content Grid -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Image Section -->
                <Card v-if="exercise.image_path || exercise.media.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ImageIcon class="h-5 w-5" />
                            Imagen
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="aspect-video w-full overflow-hidden rounded-lg bg-muted">
                            <img v-if="exercise.image_path" :src="exercise.image_path" :alt="exercise.name" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full items-center justify-center text-muted-foreground">
                                <ImageIcon class="h-16 w-16" />
                            </div>
                        </div>

                        <!-- Additional Media -->
                        <div v-if="exercise.media.length > 0" class="mt-4 grid grid-cols-3 gap-2">
                            <div v-for="media in exercise.media" :key="media.id" class="group relative aspect-square overflow-hidden rounded-md bg-muted">
                                <img :src="media.url" :alt="`${exercise.name} - ${media.type}`" class="h-full w-full object-cover" />
                                <button
                                    @click="deleteMedia(media.id)"
                                    class="absolute right-1 top-1 rounded-full bg-destructive/80 p-1 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <Trash2 class="h-3 w-3 text-white" />
                                </button>
                            </div>
                        </div>
                        <Button variant="outline" size="sm" class="mt-4" @click="openMediaDialog">
                            <Plus class="mr-2 h-4 w-4" />
                            Agregar media
                        </Button>
                    </CardContent>
                </Card>

                <!-- Equipment -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Wrench class="h-5 w-5" />
                            Equipamiento
                        </CardTitle>
                        <Button variant="ghost" size="icon" @click="openEquipmentDialog">
                            <Pencil class="h-4 w-4" />
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="exercise.equipment.length > 0" class="flex flex-wrap gap-2">
                            <Badge v-for="item in exercise.equipment" :key="item" variant="outline">
                                {{ item }}
                            </Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No hay equipamiento asignado</p>
                    </CardContent>
                </Card>

                <!-- Tags -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Tag class="h-5 w-5" />
                            Etiquetas
                        </CardTitle>
                        <Button variant="ghost" size="icon" @click="openTagsDialog">
                            <Pencil class="h-4 w-4" />
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="exercise.tags.length > 0" class="flex flex-wrap gap-2">
                            <Badge v-for="tag in exercise.tags" :key="tag" variant="secondary">
                                {{ tag }}
                            </Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No hay etiquetas asignadas</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State Cards for Adding Content -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Add Equipment -->
                <Card
                    v-if="exercise.equipment.length === 0"
                    class="cursor-pointer border-2 border-dashed transition-colors hover:border-primary/50 hover:bg-muted/50"
                    @click="openEquipmentDialog"
                >
                    <CardContent class="flex flex-col items-center justify-center py-8 text-center">
                        <Wrench class="mb-2 h-8 w-8 text-muted-foreground" />
                        <p class="text-sm font-medium text-muted-foreground">+ Agregar equipamiento</p>
                    </CardContent>
                </Card>

                <!-- Add Tags -->
                <Card
                    v-if="exercise.tags.length === 0"
                    class="cursor-pointer border-2 border-dashed transition-colors hover:border-primary/50 hover:bg-muted/50"
                    @click="openTagsDialog"
                >
                    <CardContent class="flex flex-col items-center justify-center py-8 text-center">
                        <Tag class="mb-2 h-8 w-8 text-muted-foreground" />
                        <p class="text-sm font-medium text-muted-foreground">+ Agregar etiquetas</p>
                    </CardContent>
                </Card>

                <!-- Add Instructions -->
                <Card
                    v-if="!currentInstructions"
                    class="cursor-pointer border-2 border-dashed transition-colors hover:border-primary/50 hover:bg-muted/50"
                    @click="openInstructionsDialog"
                >
                    <CardContent class="flex flex-col items-center justify-center py-8 text-center">
                        <ListOrdered class="mb-2 h-8 w-8 text-muted-foreground" />
                        <p class="text-sm font-medium text-muted-foreground">+ Agregar instrucciones</p>
                    </CardContent>
                </Card>

                <!-- Add Media -->
                <Card
                    v-if="exercise.media.length === 0 && !exercise.image_path"
                    class="cursor-pointer border-2 border-dashed transition-colors hover:border-primary/50 hover:bg-muted/50"
                    @click="openMediaDialog"
                >
                    <CardContent class="flex flex-col items-center justify-center py-8 text-center">
                        <ImageIcon class="mb-2 h-8 w-8 text-muted-foreground" />
                        <p class="text-sm font-medium text-muted-foreground">+ Agregar imagen/video</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Instructions Section -->
            <Card v-if="currentInstructions">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <ListOrdered class="h-5 w-5" />
                            Instrucciones
                        </CardTitle>
                        <CardDescription> Sigue estos pasos para realizar el ejercicio correctamente </CardDescription>
                    </div>
                    <Button variant="ghost" size="icon" @click="openInstructionsDialog">
                        <Pencil class="h-4 w-4" />
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Setup -->
                    <div v-if="currentInstructions.setup" class="space-y-2">
                        <h3 class="font-semibold">Preparación</h3>
                        <p class="text-sm text-muted-foreground">{{ currentInstructions.setup }}</p>
                    </div>

                    <!-- Execution Steps -->
                    <div v-if="currentInstructions.execution_steps && currentInstructions.execution_steps.length > 0" class="space-y-2">
                        <h3 class="font-semibold">Pasos de ejecución</h3>
                        <ol class="list-inside list-decimal space-y-1">
                            <li v-for="(step, index) in currentInstructions.execution_steps" :key="index" class="text-sm text-muted-foreground">
                                {{ step }}
                            </li>
                        </ol>
                    </div>

                    <!-- Common Mistakes -->
                    <div v-if="currentInstructions.common_mistakes" class="space-y-2">
                        <h3 class="font-semibold">Errores comunes</h3>
                        <p class="text-sm text-muted-foreground">{{ currentInstructions.common_mistakes }}</p>
                    </div>

                    <!-- Cues -->
                    <div v-if="currentInstructions.cues" class="space-y-2">
                        <h3 class="font-semibold">Indicaciones</h3>
                        <p class="text-sm text-muted-foreground">{{ currentInstructions.cues }}</p>
                    </div>

                    <!-- Breathing -->
                    <div v-if="currentInstructions.breathing" class="space-y-2">
                        <h3 class="font-semibold">Respiración</h3>
                        <p class="text-sm text-muted-foreground">{{ currentInstructions.breathing }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Legacy Instructions Section (old format) -->
            <Card v-else-if="exercise.instructions.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ListOrdered class="h-5 w-5" />
                        Instrucciones
                    </CardTitle>
                    <CardDescription> Sigue estos pasos para realizar el ejercicio correctamente </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div v-for="instructionSet in exercise.instructions" :key="instructionSet.set_name" class="space-y-3">
                        <h3 v-if="instructionSet.set_name" class="text-lg font-semibold">
                            {{ instructionSet.set_name }}
                        </h3>
                        <ol class="list-inside list-decimal space-y-2">
                            <li
                                v-for="instruction in instructionSet.instructions"
                                :key="instruction.step"
                                class="text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ instruction.text }}
                            </li>
                        </ol>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Muscle Group Dialog -->
        <Dialog v-model:open="showMuscleGroupDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Seleccionar grupos musculares</DialogTitle>
                    <DialogDescription>Elegí uno o más grupos musculares para este ejercicio</DialogDescription>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-3 overflow-y-auto py-4">
                    <div v-for="category in availableCategories.muscle_group" :key="category.id" class="flex items-center space-x-2">
                        <Checkbox
                            :id="`muscle-${category.id}`"
                            :checked="categoryForm.category_ids.includes(category.id)"
                            @update:checked="toggleCategory(category.id)"
                        />
                        <Label :for="`muscle-${category.id}`" class="cursor-pointer text-sm font-normal">
                            {{ category.label }}
                        </Label>
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline" type="button">Cancelar</Button>
                    </DialogClose>
                    <Button @click="saveCategories" :disabled="categoryForm.processing">
                        {{ categoryForm.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Movement Pattern Dialog -->
        <Dialog v-model:open="showMovementPatternDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Seleccionar patrones de movimiento</DialogTitle>
                    <DialogDescription>Elegí uno o más patrones de movimiento para este ejercicio</DialogDescription>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-3 overflow-y-auto py-4">
                    <div v-for="category in availableCategories.movement_pattern" :key="category.id" class="flex items-center space-x-2">
                        <Checkbox
                            :id="`pattern-${category.id}`"
                            :checked="categoryForm.category_ids.includes(category.id)"
                            @update:checked="toggleCategory(category.id)"
                        />
                        <Label :for="`pattern-${category.id}`" class="cursor-pointer text-sm font-normal">
                            {{ category.label }}
                        </Label>
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline" type="button">Cancelar</Button>
                    </DialogClose>
                    <Button @click="saveCategories" :disabled="categoryForm.processing">
                        {{ categoryForm.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Difficulty Dialog -->
        <Dialog v-model:open="showDifficultyDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Seleccionar dificultad</DialogTitle>
                    <DialogDescription>Elegí uno o más niveles de dificultad para este ejercicio</DialogDescription>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-3 overflow-y-auto py-4">
                    <div v-for="category in availableCategories.difficulty" :key="category.id" class="flex items-center space-x-2">
                        <Checkbox
                            :id="`difficulty-${category.id}`"
                            :checked="categoryForm.category_ids.includes(category.id)"
                            @update:checked="toggleCategory(category.id)"
                        />
                        <Label :for="`difficulty-${category.id}`" class="cursor-pointer text-sm font-normal">
                            {{ category.label }}
                        </Label>
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline" type="button">Cancelar</Button>
                    </DialogClose>
                    <Button @click="saveCategories" :disabled="categoryForm.processing">
                        {{ categoryForm.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Exercise Dialog -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Editar ejercicio</DialogTitle>
                    <DialogDescription>Modificá los detalles del ejercicio</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveExercise" class="space-y-4 py-4">
                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="edit-name">Nombre *</Label>
                        <Input id="edit-name" v-model="updateForm.name" placeholder="Ej: Press Banca" />
                        <p v-if="updateForm.errors.name" class="text-sm text-destructive">{{ updateForm.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <Label for="edit-description">Descripción</Label>
                        <Textarea id="edit-description" v-model="updateForm.description" placeholder="Descripción del ejercicio" rows="3" />
                        <p v-if="updateForm.errors.description" class="text-sm text-destructive">{{ updateForm.errors.description }}</p>
                    </div>

                    <!-- Image Path -->
                    <div class="space-y-2">
                        <Label for="edit-image">URL de imagen</Label>
                        <Input id="edit-image" v-model="updateForm.image_path" type="url" placeholder="https://..." />
                        <p v-if="updateForm.errors.image_path" class="text-sm text-destructive">{{ updateForm.errors.image_path }}</p>
                    </div>

                    <!-- Alternative Names -->
                    <div class="space-y-2">
                        <Label>Nombres alternativos</Label>
                        <div class="flex gap-2">
                            <Input v-model="newAlternativeName" placeholder="Agregar nombre alternativo" @keyup.enter.prevent="addAlternativeName" />
                            <Button type="button" size="sm" @click="addAlternativeName">Agregar</Button>
                        </div>
                        <div v-if="updateForm.alternative_names.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <Badge v-for="(name, index) in updateForm.alternative_names" :key="index" variant="secondary" class="gap-1">
                                {{ name }}
                                <button type="button" @click="removeAlternativeName(index)" class="ml-1 hover:text-destructive">
                                    <X class="h-3 w-3" />
                                </button>
                            </Badge>
                        </div>
                        <p v-if="updateForm.errors.alternative_names" class="text-sm text-destructive">{{ updateForm.errors.alternative_names }}</p>
                    </div>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="outline" type="button">Cancelar</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="updateForm.processing">
                            {{ updateForm.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Equipment Dialog -->
        <Dialog v-model:open="showEquipmentDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Seleccionar equipamiento</DialogTitle>
                    <DialogDescription>Elegí el equipamiento necesario para este ejercicio</DialogDescription>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-3 overflow-y-auto py-4">
                    <div v-for="equipment in availableEquipment" :key="equipment.id" class="flex items-center space-x-2">
                        <Checkbox
                            :id="`equipment-${equipment.id}`"
                            :checked="equipmentForm.equipment_ids.includes(equipment.id)"
                            @update:checked="toggleEquipment(equipment.id)"
                        />
                        <Label :for="`equipment-${equipment.id}`" class="cursor-pointer text-sm font-normal">
                            {{ equipment.label }}
                        </Label>
                    </div>
                    <p v-if="availableEquipment.length === 0" class="text-sm text-muted-foreground">No hay equipamiento disponible</p>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline" type="button">Cancelar</Button>
                    </DialogClose>
                    <Button @click="saveEquipment" :disabled="equipmentForm.processing">
                        {{ equipmentForm.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Tags Dialog -->
        <Dialog v-model:open="showTagsDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Seleccionar etiquetas</DialogTitle>
                    <DialogDescription>Elegí las etiquetas para este ejercicio</DialogDescription>
                </DialogHeader>
                <div class="max-h-[60vh] space-y-3 overflow-y-auto py-4">
                    <div v-for="tag in availableTags" :key="tag.id" class="flex items-center space-x-2">
                        <Checkbox :id="`tag-${tag.id}`" :checked="tagsForm.tag_ids.includes(tag.id)" @update:checked="toggleTag(tag.id)" />
                        <Label :for="`tag-${tag.id}`" class="cursor-pointer text-sm font-normal">
                            {{ tag.label }}
                        </Label>
                    </div>
                    <p v-if="availableTags.length === 0" class="text-sm text-muted-foreground">No hay etiquetas disponibles</p>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline" type="button">Cancelar</Button>
                    </DialogClose>
                    <Button @click="saveTags" :disabled="tagsForm.processing">
                        {{ tagsForm.processing ? 'Guardando...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Media Dialog -->
        <Dialog v-model:open="showMediaDialog">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Agregar imagen o video</DialogTitle>
                    <DialogDescription>Agregá una URL de imagen o video para este ejercicio</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveMedia" class="space-y-4 py-4">
                    <!-- URL -->
                    <div class="space-y-2">
                        <Label for="media-url">URL *</Label>
                        <Input id="media-url" v-model="mediaForm.url" type="url" placeholder="https://..." />
                        <p v-if="mediaForm.errors.url" class="text-sm text-destructive">{{ mediaForm.errors.url }}</p>
                    </div>

                    <!-- Media Type -->
                    <div class="space-y-2">
                        <Label for="media-type">Tipo de medio *</Label>
                        <Select v-model="mediaForm.media_type_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="type in availableMediaTypes" :key="type.id" :value="type.id">
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="mediaForm.errors.media_type_id" class="text-sm text-destructive">{{ mediaForm.errors.media_type_id }}</p>
                    </div>

                    <!-- Provider -->
                    <div class="space-y-2">
                        <Label for="media-provider">Proveedor (opcional)</Label>
                        <Select v-model="mediaForm.provider">
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar proveedor" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="youtube">YouTube</SelectItem>
                                <SelectItem value="vimeo">Vimeo</SelectItem>
                                <SelectItem value="s3">Amazon S3</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Is Primary -->
                    <div class="flex items-center space-x-2">
                        <Checkbox id="media-primary" v-model:checked="mediaForm.is_primary" />
                        <Label for="media-primary" class="cursor-pointer text-sm font-normal"> Marcar como principal </Label>
                    </div>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="outline" type="button">Cancelar</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="mediaForm.processing">
                            {{ mediaForm.processing ? 'Guardando...' : 'Agregar' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Instructions Dialog -->
        <Dialog v-model:open="showInstructionsDialog">
            <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar instrucciones</DialogTitle>
                    <DialogDescription>Agregá o modificá las instrucciones para este ejercicio</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveInstructions" class="space-y-4 py-4">
                    <!-- Setup -->
                    <div class="space-y-2">
                        <Label for="instructions-setup">Preparación</Label>
                        <Textarea id="instructions-setup" v-model="instructionsForm.setup" placeholder="Describe cómo prepararse para el ejercicio..." rows="3" />
                        <p v-if="instructionsForm.errors.setup" class="text-sm text-destructive">{{ instructionsForm.errors.setup }}</p>
                    </div>

                    <!-- Execution Steps -->
                    <div class="space-y-2">
                        <Label>Pasos de ejecución</Label>
                        <div class="flex gap-2">
                            <Input v-model="newExecutionStep" placeholder="Agregar paso" @keyup.enter.prevent="addExecutionStep" />
                            <Button type="button" size="sm" @click="addExecutionStep">Agregar</Button>
                        </div>
                        <div v-if="instructionsForm.execution_steps.length > 0" class="mt-2 space-y-2">
                            <div
                                v-for="(step, index) in instructionsForm.execution_steps"
                                :key="index"
                                class="flex items-center gap-2 rounded-md border bg-muted/50 p-2"
                            >
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-xs text-primary-foreground">
                                    {{ index + 1 }}
                                </span>
                                <span class="flex-1 text-sm">{{ step }}</span>
                                <button type="button" @click="removeExecutionStep(index)" class="text-muted-foreground hover:text-destructive">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <p v-if="instructionsForm.errors.execution_steps" class="text-sm text-destructive">{{ instructionsForm.errors.execution_steps }}</p>
                    </div>

                    <!-- Common Mistakes -->
                    <div class="space-y-2">
                        <Label for="instructions-mistakes">Errores comunes</Label>
                        <Textarea
                            id="instructions-mistakes"
                            v-model="instructionsForm.common_mistakes"
                            placeholder="Describe errores comunes a evitar..."
                            rows="3"
                        />
                        <p v-if="instructionsForm.errors.common_mistakes" class="text-sm text-destructive">{{ instructionsForm.errors.common_mistakes }}</p>
                    </div>

                    <!-- Cues -->
                    <div class="space-y-2">
                        <Label for="instructions-cues">Indicaciones</Label>
                        <Textarea id="instructions-cues" v-model="instructionsForm.cues" placeholder="Indicaciones verbales para guiar la ejecución..." rows="3" />
                        <p v-if="instructionsForm.errors.cues" class="text-sm text-destructive">{{ instructionsForm.errors.cues }}</p>
                    </div>

                    <!-- Breathing -->
                    <div class="space-y-2">
                        <Label for="instructions-breathing">Respiración</Label>
                        <Textarea
                            id="instructions-breathing"
                            v-model="instructionsForm.breathing"
                            placeholder="Describe el patrón de respiración recomendado..."
                            rows="2"
                        />
                        <p v-if="instructionsForm.errors.breathing" class="text-sm text-destructive">{{ instructionsForm.errors.breathing }}</p>
                    </div>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="outline" type="button">Cancelar</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="instructionsForm.processing">
                            {{ instructionsForm.processing ? 'Guardando...' : 'Guardar instrucciones' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppSidebarLayout>
</template>
