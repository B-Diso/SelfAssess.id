<script setup lang="ts">
import { AccordionContent } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <AccordionContent
    :class="
      cn(
        'overflow-hidden text-sm transition-all data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down',
        props.class
      )
    "
  >
    <div class="pb-4 pt-0">
      <slot />
    </div>
  </AccordionContent>
</template>
