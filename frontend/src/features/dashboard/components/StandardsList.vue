<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { Button } from '@/components/ui/button'
import { BookOpen, ArrowRight, ChevronRight } from 'lucide-vue-next'
import type { Standard } from '@/features/standard/types/standard'

interface Props {
  standards: Standard[] | undefined
  loading?: boolean
}

defineProps<Props>()

const getStandardTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    'gia': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    'quality': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    'risk': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    'other': 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-300',
  }
  return classes[type?.toLowerCase()] || classes['other']
}

const formatType = (type: string) => {
  return type?.charAt(0).toUpperCase() + type?.slice(1) || 'Standard'
}
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle class="text-lg font-semibold flex items-center gap-2">
        <BookOpen class="h-5 w-5" />
        Active Standards
      </CardTitle>
      <Button variant="ghost" size="sm" as-child>
        <router-link to="/standards" class="flex items-center gap-1">
          View All
          <ArrowRight class="h-4 w-4" />
        </router-link>
      </Button>
    </CardHeader>
    <CardContent>
      <!-- Loading State -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 4" :key="i" class="flex items-center gap-3 p-3">
          <Skeleton class="h-10 w-10 rounded-lg" />
          <div class="flex-1 space-y-2">
            <Skeleton class="h-4 w-2/3" />
            <Skeleton class="h-3 w-1/3" />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!standards || standards.length === 0" class="text-center py-8 text-muted-foreground">
        <BookOpen class="h-8 w-8 mx-auto mb-2 opacity-50" />
        <p>No active standards</p>
      </div>

      <!-- Data State -->
      <div v-else class="space-y-2">
        <div
          v-for="standard in standards.slice(0, 5)"
          :key="standard.id"
          class="flex items-center gap-3 p-3 rounded-lg hover:bg-accent/50 transition-colors cursor-pointer"
          @click="$router.push(`/standards/${standard.id}/organizations`)"
        >
          <!-- Standard Icon -->
          <div
            class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0 bg-primary text-primary-foreground font-bold text-sm"
          >
            {{ standard.name?.charAt(0) || 'S' }}
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm truncate group-hover:text-primary transition-colors">
              {{ standard.name }}
            </p>
            <p class="text-xs text-muted-foreground flex items-center gap-2">
              <span 
                class="px-1.5 py-0.5 rounded text-[10px] font-medium capitalize"
                :class="getStandardTypeBadgeClass(standard.type)"
              >
                {{ formatType(standard.type) }}
              </span>
              <span v-if="standard.version">v{{ standard.version }}</span>
            </p>
          </div>

          <!-- Chevron Icon -->
          <ChevronRight class="h-4 w-4 text-muted-foreground shrink-0" />
        </div>
      </div>
    </CardContent>
  </Card>
</template>
