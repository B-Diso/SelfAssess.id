<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { useVModel } from '@vueuse/core'
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  modelValue?: string
  disabled?: boolean
  placeholder?: string
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  rows: 3,
})

const emits = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const value = useVModel(props, 'modelValue', emits, {
  passive: true,
})

const classes = computed(() =>
  cn(
    'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    props.class
  )
)
</script>

<template>
  <textarea
    v-model="value"
    :class="classes"
    :disabled="disabled"
    :placeholder="placeholder"
    :rows="rows"
  ></textarea>
</template>
