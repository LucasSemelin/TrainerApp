<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Primitive, type PrimitiveProps } from 'reka-ui'

interface Props extends PrimitiveProps {
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
})
</script>

<template>
  <Primitive
    data-slot="button-secondary"
    :as="as"
    :as-child="asChild"
    :class="[
      // Estilos secundarios explícitos
      'inline-flex h-10 items-center justify-center rounded-md border border-primary/30 bg-transparent px-4 py-2 text-sm font-medium text-primary ring-offset-background transition-colors hover:border-primary/40 hover:bg-primary/5 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
      props.class
    ]"
  >
    <slot />
  </Primitive>
</template>
