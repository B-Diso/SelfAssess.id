<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { Activity, FileText, User, CheckCircle, Upload } from 'lucide-vue-next'
import type { Activity as ActivityType } from '../types/dashboard'

interface Props {
  activities: ActivityType[] | undefined
  loading?: boolean
}

defineProps<Props>()

const formatTimeAgo = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`
  
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
}

const getActivityIcon = (type: ActivityType['type']) => {
  switch (type) {
    case 'assessment_created':
    case 'assessment_submitted':
    case 'assessment_reviewed':
      return FileText
    case 'user_login':
    case 'user_created':
      return User
    case 'action_plan_created':
    case 'action_plan_completed':
      return CheckCircle
    case 'evidence_uploaded':
      return Upload
    default:
      return Activity
  }
}

const getActivityColor = (type: ActivityType['type']) => {
  switch (type) {
    case 'assessment_created':
      return 'bg-blue-100 text-blue-600'
    case 'assessment_submitted':
      return 'bg-amber-100 text-amber-600'
    case 'assessment_reviewed':
      return 'bg-violet-100 text-violet-600'
    case 'user_login':
      return 'bg-gray-100 text-gray-600'
    case 'user_created':
      return 'bg-green-100 text-green-600'
    case 'action_plan_created':
      return 'bg-orange-100 text-orange-600'
    case 'action_plan_completed':
      return 'bg-emerald-100 text-emerald-600'
    case 'evidence_uploaded':
      return 'bg-cyan-100 text-cyan-600'
    default:
      return 'bg-gray-100 text-gray-600'
  }
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="text-lg font-semibold flex items-center gap-2">
        <Activity class="h-5 w-5" />
        Recent Activity
      </CardTitle>
    </CardHeader>
    <CardContent>
      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 5" :key="i" class="flex items-start gap-3">
          <Skeleton class="h-8 w-8 rounded-full" />
          <div class="flex-1 space-y-2">
            <Skeleton class="h-4 w-3/4" />
            <Skeleton class="h-3 w-1/2" />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!activities || activities.length === 0" class="text-center py-8 text-muted-foreground">
        <Activity class="h-8 w-8 mx-auto mb-2 opacity-50" />
        <p>No recent activity</p>
      </div>

      <!-- Data State -->
      <div v-else class="max-h-[400px] overflow-y-auto space-y-4 pr-2">
        <div
          v-for="activity in activities"
          :key="activity.id"
          class="flex items-start gap-3"
        >
          <div
            class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
            :class="getActivityColor(activity.type)"
          >
            <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">{{ activity.description }}</p>
            <div class="flex items-center gap-2 text-xs text-muted-foreground mt-0.5">
              <span v-if="activity.user">{{ activity.user.name }}</span>
              <span v-if="activity.user && activity.organization">•</span>
              <span v-if="activity.organization">{{ activity.organization.name }}</span>
              <span>•</span>
              <span>{{ formatTimeAgo(activity.createdAt) }}</span>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
