<script setup lang="ts">
import { cn } from "@/lib/utils";

interface Props {
  title: string;
  description?: string;
  class?: string;
}

defineProps<Props>();
</script>

<template>
  <div
    :class="
      cn('shrink-0 bg-white rounded-lg shadow-sm border p-4 mb-4', $props.class)
    "
  >
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h2
          class="text-lg font-semibold leading-none tracking-tight text-slate-900"
        >
          {{ title }}
        </h2>
        <p
          v-if="description"
          class="text-xs text-muted-foreground mt-1.5 leading-relaxed"
        >
          {{ description }}
        </p>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
