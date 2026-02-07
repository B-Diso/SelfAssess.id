<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { AlertCircle, Calendar, User } from 'lucide-vue-next'
import type { ActionPlanSummary } from '../types/dashboard'

interface Props {
  data: ActionPlanSummary | undefined
  loading?: boolean
}

defineProps<Props>()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getDueDateClasses = (daysOverdue: number, daysRemaining: number) => {
  if (daysOverdue > 0) return 'bg-destructive text-destructive-foreground hover:bg-destructive'
  if (daysRemaining <= 3) return 'bg-amber-500 text-white hover:bg-amber-500'
  return 'bg-secondary text-secondary-foreground hover:bg-secondary'
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="text-lg font-semibold flex items-center gap-2">
        <AlertCircle class="h-5 w-5" />
        Action Plans
      </CardTitle>
    </CardHeader>
    <CardContent>
      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="p-3 border rounded-lg">
          <Skeleton class="h-4 w-3/4 mb-2" />
          <Skeleton class="h-3 w-1/2" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!data || (data.overdue.length === 0 && data.upcoming.length === 0)" class="text-center py-8 text-muted-foreground">
        <AlertCircle class="h-8 w-8 mx-auto mb-2 opacity-50" />
        <p>No action plans requiring attention</p>
      </div>

      <!-- Data State -->
      <div v-else class="max-h-[400px] overflow-y-auto space-y-4 pr-2">
        <!-- Overdue Section -->
        <div v-if="data.overdue.length > 0">
          <h4 class="text-sm font-medium text-destructive mb-2">Overdue</h4>
          <div class="space-y-2">
            <div
              v-for="plan in data.overdue"
              :key="plan.id"
              class="p-3 border border-destructive/20 rounded-lg bg-destructive/5"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                  <p class="font-medium text-sm truncate">{{ plan.title }}</p>
                  <p class="text-xs text-muted-foreground truncate">{{ plan.assessment.name }}</p>
                </div>
                <Badge variant="destructive" class="shrink-0">
                  {{ plan.daysOverdue }} hari overdue
                </Badge>
              </div>
              <div class="flex items-center gap-4 mt-2 text-xs text-muted-foreground">
                <span class="flex items-center gap-1">
                  <Calendar class="h-3 w-3" />
                  {{ formatDate(plan.dueDate) }}
                </span>
                <span v-if="plan.pic" class="flex items-center gap-1">
                  <User class="h-3 w-3" />
                  {{ plan.pic.name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Upcoming Section -->
        <div v-if="data.upcoming.length > 0">
          <h4 class="text-sm font-medium text-amber-600 mb-2">Upcoming</h4>
          <div class="space-y-2">
            <div
              v-for="plan in data.upcoming"
              :key="plan.id"
              class="p-3 border rounded-lg hover:bg-accent/50 transition-colors"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                  <p class="font-medium text-sm truncate">{{ plan.title }}</p>
                  <p class="text-xs text-muted-foreground truncate">{{ plan.assessment.name }}</p>
                </div>
                <Badge :class="getDueDateClasses(0, plan.daysRemaining)" class="shrink-0">
                  {{ plan.daysRemaining }} hari lagi
                </Badge>
              </div>
              <div class="flex items-center gap-4 mt-2 text-xs text-muted-foreground">
                <span class="flex items-center gap-1">
                  <Calendar class="h-3 w-3" />
                  {{ formatDate(plan.dueDate) }}
                </span>
                <span v-if="plan.pic" class="flex items-center gap-1">
                  <User class="h-3 w-3" />
                  {{ plan.pic.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
