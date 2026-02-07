<script setup lang="ts">
import { AccordionItem, type AccordionItemProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props extends AccordionItemProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <AccordionItem
    :value="props.value"
    :disabled="props.disabled"
    :class="cn('border-b', props.class)"
  >
    <slot />
  </AccordionItem>
</template>
