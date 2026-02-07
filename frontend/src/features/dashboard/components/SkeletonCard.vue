<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
</script>

<template>
  <Card>
    <CardContent class="p-6">
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <Skeleton class="h-4 w-24" />
          <Skeleton class="h-8 w-16" />
          <Skeleton class="h-3 w-32" />
        </div>
        <Skeleton class="h-12 w-12 rounded-full shrink-0" />
      </div>
    </CardContent>
  </Card>
</template>
