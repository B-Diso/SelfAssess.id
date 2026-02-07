<script setup lang="ts">
import type { Component } from 'vue'
import { Card, CardContent } from '@/components/ui/card'

interface Props {
  title: string
  value: string | number
  subtitle?: string
  icon: Component
  iconBgClass?: string
  iconColorClass?: string
}

withDefaults(defineProps<Props>(), {
  iconBgClass: 'bg-primary/10',
  iconColorClass: 'text-primary',
})
</script>

<template>
  <Card>
    <CardContent class="p-6">
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
          <p class="text-3xl font-bold tracking-tight">{{ value }}</p>
          <p v-if="subtitle" class="text-xs text-muted-foreground">{{ subtitle }}</p>
        </div>
        <div
          class="p-3 rounded-full shrink-0"
          :class="iconBgClass"
        >
          <component :is="icon" :class="['h-6 w-6', iconColorClass]" />
        </div>
      </div>
    </CardContent>
  </Card>
</template>
