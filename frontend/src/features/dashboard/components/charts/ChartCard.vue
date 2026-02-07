<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

interface Props {
  title: string
  subtitle?: string
  loading?: boolean
}

defineProps<Props>()
</script>

<template>
  <Card>
    <CardHeader class="pb-2">
      <CardTitle class="text-lg font-semibold">{{ title }}</CardTitle>
      <p v-if="subtitle" class="text-sm text-muted-foreground">{{ subtitle }}</p>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="h-64 flex items-center justify-center">
        <div class="animate-pulse flex flex-col items-center gap-2">
          <div class="h-32 w-32 rounded-full bg-primary/10" />
          <div class="h-4 w-24 rounded bg-primary/10" />
        </div>
      </div>
      <slot v-else />
    </CardContent>
  </Card>
</template>
