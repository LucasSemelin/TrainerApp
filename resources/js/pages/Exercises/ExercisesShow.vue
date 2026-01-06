<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Dumbbell, Image as ImageIcon, ListOrdered, Tag, Wrench } from 'lucide-vue-next';

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
}

const props = defineProps<{
    exercise: Exercise;
}>();

const breadcrumbs = [
    { label: 'Ejercicios', href: '/exercises' },
    { label: props.exercise.name, href: '#' },
];
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
                    <div class="flex-1 space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight">{{ exercise.name }}</h1>
                        <div v-if="exercise.alternative_names.length > 0" class="flex flex-wrap gap-1 text-sm text-muted-foreground">
                            <span>También conocido como:</span>
                            <span class="italic">{{ exercise.alternative_names.join(', ') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Categories Badges -->
                <div class="flex flex-wrap gap-2">
                    <!-- Muscle Groups -->
                    <template v-if="exercise.categories.muscle_group && exercise.categories.muscle_group.length > 0">
                        <Badge v-for="cat in exercise.categories.muscle_group" :key="cat" variant="default">
                            {{ cat }}
                        </Badge>
                    </template>
                    <Badge v-else variant="secondary" class="cursor-pointer hover:bg-secondary/80"> + Grupo muscular </Badge>

                    <!-- Movement Patterns -->
                    <template v-if="exercise.categories.movement_pattern && exercise.categories.movement_pattern.length > 0">
                        <Badge v-for="cat in exercise.categories.movement_pattern" :key="cat" variant="secondary">
                            {{ cat }}
                        </Badge>
                    </template>
                    <Badge v-else variant="secondary" class="cursor-pointer hover:bg-secondary/80"> + Patrón de movimiento </Badge>

                    <!-- Difficulty -->
                    <template v-if="exercise.categories.difficulty && exercise.categories.difficulty.length > 0">
                        <Badge v-for="cat in exercise.categories.difficulty" :key="cat" variant="outline">
                            {{ cat }}
                        </Badge>
                    </template>
                    <Badge v-else variant="secondary" class="cursor-pointer hover:bg-secondary/80"> + Dificultad </Badge>
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
                            <div v-for="media in exercise.media" :key="media.id" class="aspect-square overflow-hidden rounded-md bg-muted">
                                <img :src="media.url" :alt="`${exercise.name} - ${media.type}`" class="h-full w-full object-cover" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Equipment -->
                <Card v-if="exercise.equipment.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Wrench class="h-5 w-5" />
                            Equipamiento
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="item in exercise.equipment" :key="item" variant="outline">
                                {{ item }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Tags -->
                <Card v-if="exercise.tags.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Tag class="h-5 w-5" />
                            Etiquetas
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="tag in exercise.tags" :key="tag" variant="secondary">
                                {{ tag }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State Cards for Adding Content -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Add Equipment -->
                <Card
                    v-if="exercise.equipment.length === 0"
                    class="cursor-pointer border-2 border-dashed transition-colors hover:border-primary/50 hover:bg-muted/50"
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
                >
                    <CardContent class="flex flex-col items-center justify-center py-8 text-center">
                        <Tag class="mb-2 h-8 w-8 text-muted-foreground" />
                        <p class="text-sm font-medium text-muted-foreground">+ Agregar etiquetas</p>
                    </CardContent>
                </Card>

                <!-- Add Instructions -->
                <Card
                    v-if="exercise.instructions.length === 0"
                    class="cursor-pointer border-2 border-dashed transition-colors hover:border-primary/50 hover:bg-muted/50"
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
                >
                    <CardContent class="flex flex-col items-center justify-center py-8 text-center">
                        <ImageIcon class="mb-2 h-8 w-8 text-muted-foreground" />
                        <p class="text-sm font-medium text-muted-foreground">+ Agregar imagen/video</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Instructions Section -->
            <Card v-if="exercise.instructions.length > 0">
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
    </AppSidebarLayout>
</template>
