<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

import { Skeleton } from '@/components/ui/skeleton'
import { Button } from '@/components/ui/button'
import { FileText, ArrowRight } from 'lucide-vue-next'
import type { RecentAssessment } from '../types/dashboard'
import { STATUS_COLORS } from '../types/dashboard'

interface Props {
  assessments: RecentAssessment[] | undefined
  loading?: boolean
  showOrganization?: boolean
}

defineProps<Props>()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusColor = (status: string) => {
  return STATUS_COLORS[status.toLowerCase()] || '#9CA3AF'
}
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle class="text-lg font-semibold flex items-center gap-2">
        <FileText class="h-5 w-5" />
        Recent Assessments
      </CardTitle>
      <Button variant="ghost" size="sm" as-child>
        <router-link to="/assessments" class="flex items-center gap-1">
          View All
          <ArrowRight class="h-4 w-4" />
        </router-link>
      </Button>
    </CardHeader>
    <CardContent>
      <!-- Loading State -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 5" :key="i" class="flex items-center gap-4 p-3">
          <Skeleton class="h-2.5 w-2.5 rounded-full" />
          <div class="flex-1 space-y-2">
            <Skeleton class="h-4 w-1/3" />
            <Skeleton class="h-3 w-1/4" />
          </div>
          <div class="text-right space-y-1">
            <Skeleton class="h-4 w-8" />
            <Skeleton class="h-3 w-12" />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!assessments || assessments.length === 0" class="text-center py-8 text-muted-foreground">
        <FileText class="h-8 w-8 mx-auto mb-2 opacity-50" />
        <p>No assessments found</p>
      </div>

      <!-- Data State -->
      <div v-else class="space-y-2">
        <div
          v-for="assessment in assessments"
          :key="assessment.id"
          class="flex items-center gap-4 p-3 rounded-lg hover:bg-accent/50 transition-colors cursor-pointer"
          @click="$router.push(`/assessments/${assessment.id}`)"
        >
          <!-- Status Indicator -->
          <div
            class="h-2.5 w-2.5 rounded-full shrink-0"
            :style="{ backgroundColor: getStatusColor(assessment.status) }"
          />

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm truncate">{{ assessment.name }}</p>
            <p v-if="showOrganization && assessment.organizationName" class="text-xs text-muted-foreground">
              {{ assessment.organizationName }}
            </p>
            <p v-else class="text-xs text-muted-foreground">
              Created {{ formatDate(assessment.createdAt) }}
            </p>
          </div>

          <!-- Progress -->
          <div class="text-right shrink-0">
            <p class="text-sm font-medium">{{ assessment.progress }}%</p>
            <p class="text-xs text-muted-foreground capitalize">{{ assessment.status }}</p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
