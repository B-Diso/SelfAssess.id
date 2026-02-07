<script setup lang="ts">
import { AccordionHeader, AccordionTrigger } from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <AccordionHeader class="flex">
    <AccordionTrigger
      :class="
        cn(
          'flex flex-1 items-center justify-between py-4 font-medium transition-all hover:underline [&[data-state=open]>svg]:rotate-180',
          props.class
        )
      "
    >
      <slot />
      <ChevronDown
        class="h-4 w-4 shrink-0 transition-transform duration-200"
      />
    </AccordionTrigger>
  </AccordionHeader>
</template>
