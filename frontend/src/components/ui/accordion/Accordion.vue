<script setup lang="ts">
import { AccordionRoot, type AccordionRootProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props extends AccordionRootProps {
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  type: 'single',
  collapsible: true,
})
</script>

<template>
  <AccordionRoot
    :type="props.type"
    :collapsible="props.collapsible"
    :disabled="props.disabled"
    :dir="props.dir"
    :orientation="props.orientation"
    :model-value="props.modelValue"
    :class="cn('w-full', props.class)"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <slot />
  </AccordionRoot>
</template>
