<script setup lang="ts">
import { ProgressIndicator, ProgressRoot } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  modelValue?: number | null
  max?: number
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 0,
  max: 100,
})

const progressPercentage = computed(() => {
  return ((props.modelValue ?? 0) / props.max) * 100
})
</script>

<template>
  <ProgressRoot
    :model-value="props.modelValue"
    :max="props.max"
    :class="
      cn(
        'relative h-4 w-full overflow-hidden rounded-full bg-secondary',
        props.class
      )
    "
  >
    <ProgressIndicator
      class="h-full w-full flex-1 bg-primary transition-all"
      :style="`transform: translateX(-${100 - progressPercentage}%)`"
    />
  </ProgressRoot>
</template>
