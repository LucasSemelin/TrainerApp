<script setup lang="ts">
import { Dumbbell } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <Dumbbell :class="className" v-bind="$attrs" />
</template>
