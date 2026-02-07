<template>
  <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
    <SidebarTrigger class="-ml-1" />
    <Separator orientation="vertical" class="mr-2 h-4" />

    <div class="flex items-center justify-between flex-1">
      <!-- Page Title -->
      <div class="flex flex-col">
        <h2 class="text-lg font-semibold text-gray-900">
          {{ pageTitle }}
        </h2>
        <p
          v-if="pageSubtitle"
          class="text-sm text-gray-500"
        >
          {{ pageSubtitle }}
        </p>
      </div>

      <!-- Right side spacer -->
      <div class="flex items-center gap-4"></div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { SidebarTrigger } from '@/components/ui/sidebar'
import { Separator } from '@/components/ui/separator'

const route = useRoute()

const pageTitle = computed(() => {
  return (route.meta.title as string | undefined) ?? route.name?.toString() ?? 'Dashboard'
})

const pageSubtitle = computed(() => {
  return (route.meta.description as string | undefined) ?? ''
})
</script>
