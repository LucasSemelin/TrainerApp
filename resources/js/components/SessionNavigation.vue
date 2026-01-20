<script setup lang="ts">
import type { WorkoutSession } from '@/types/workout';
import { Plus } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    sessions: WorkoutSession[];
    activeSessionId: string;
}

interface Emits {
    (e: 'select', sessionId: string): void;
    (e: 'add'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const sortedSessions = computed(() => [...props.sessions].sort((a, b) => a.session_order - b.session_order));

const selectSession = (sessionId: string) => {
    emit('select', sessionId);
};

const addSession = () => {
    emit('add');
};
</script>

<template>
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <!-- Session Pills -->
        <button
            v-for="session in sortedSessions"
            :key="session.id"
            @click="selectSession(session.id)"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold transition-all"
            :class="
                session.id === activeSessionId
                    ? 'bg-primary text-primary-foreground shadow-lg'
                    : 'bg-muted text-muted-foreground hover:bg-muted/80'
            "
            :title="session.name || `Día ${session.session_order}`"
        >
            {{ session.session_order }}
        </button>

        <!-- Add Session Button -->
        <button
            @click="addSession"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-muted-foreground/30 text-muted-foreground transition-all hover:border-primary hover:text-primary"
            title="Agregar día"
        >
            <Plus class="h-5 w-5" />
        </button>
    </div>
</template>
