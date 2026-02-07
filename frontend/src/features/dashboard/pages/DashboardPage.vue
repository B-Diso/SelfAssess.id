<script setup lang="ts">
import { computed } from 'vue'
import { AlertCircle, Building2, Users, FileText, ClipboardCheck, CheckCircle, AlertTriangle, TrendingUp, UserCheck } from 'lucide-vue-next'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'

import { Button } from '@/components/ui/button'


import StatCard from '../components/StatCard.vue'
import SkeletonCard from '../components/SkeletonCard.vue'
import RecentActivity from '../components/RecentActivity.vue'
import ActionPlanList from '../components/ActionPlanList.vue'
import AssessmentTable from '../components/AssessmentTable.vue'
import QuickActions from '../components/QuickActions.vue'
import { AssessmentStatusChart } from '../components/charts'
import StandardsList from '../components/StandardsList.vue'

import { useDashboard } from '../composables/useDashboard'
import { useAuth } from '@/features/auth/composables/useAuth'

const { 
  statistics, 
  activities, 
  assessments, 
  actionPlans, 
} = useDashboard()

// Fetch active standards
import { useQuery } from '@tanstack/vue-query'
import { standardApi } from '@/features/standard/api/standardApi'

const standardsQuery = useQuery({
  queryKey: ['dashboard', 'standards'],
  queryFn: async () => {
    const response = await standardApi.getStandards({ 
      isActive: true, 
      perPage: 5,
      page: 1 
    })
    return response.data
  },
  staleTime: 5 * 60 * 1000,
})

const { isSuperAdmin, isOrganizationAdmin } = useAuth()

// ============================================================================
// Stats Configuration by Role
// ============================================================================

const superAdminStats = computed(() => {
  if (!statistics.data.value?.superAdmin) return []
  const stats = statistics.data.value.superAdmin
  return [
    {
      title: 'Total Organizations',
      value: stats.totalOrganizations,
      subtitle: 'Active organizations',
      icon: Building2,
      iconBgClass: 'bg-blue-100',
      iconColorClass: 'text-blue-600',
    },
    {
      title: 'Total Users',
      value: stats.totalUsers,
      subtitle: 'Across all organizations',
      icon: Users,
      iconBgClass: 'bg-green-100',
      iconColorClass: 'text-green-600',
    },
    {
      title: 'Active Assessments',
      value: stats.activeAssessments,
      subtitle: 'In progress assessments',
      icon: FileText,
      iconBgClass: 'bg-amber-100',
      iconColorClass: 'text-amber-600',
    },
    {
      title: 'Pending Reviews',
      value: stats.pendingReviews,
      subtitle: 'Awaiting review',
      icon: ClipboardCheck,
      iconBgClass: 'bg-violet-100',
      iconColorClass: 'text-violet-600',
    },
  ]
})

const orgAdminStats = computed(() => {
  if (!statistics.data.value?.orgAdmin) return []
  const stats = statistics.data.value.orgAdmin
  return [
    {
      title: 'Organization Users',
      value: stats.totalUsers,
      subtitle: 'Total team members in your organization',
      icon: Users,
      iconBgClass: 'bg-blue-100',
      iconColorClass: 'text-blue-600',
    },
    {
      title: 'Active Assessments',
      value: stats.activeAssessments,
      subtitle: 'Assessments currently in progress',
      icon: FileText,
      iconBgClass: 'bg-amber-100',
      iconColorClass: 'text-amber-600',
    },
    {
      title: 'Completion Rate',
      value: `${stats.completionRate}%`,
      subtitle: 'Average assessment completion rate',
      icon: TrendingUp,
      iconBgClass: 'bg-green-100',
      iconColorClass: 'text-green-600',
    },
    {
      title: 'Pending Action Plans',
      value: stats.pendingActionPlans,
      subtitle: 'Due within 7 days or overdue',
      icon: AlertTriangle,
      iconBgClass: 'bg-red-100',
      iconColorClass: 'text-red-600',
    },
  ]
})

const reviewerStats = computed(() => {
  if (!statistics.data.value?.reviewer) return []
  const stats = statistics.data.value.reviewer
  return [
    {
      title: 'Pending Reviews',
      value: stats.pendingReviews,
      subtitle: 'Awaiting your review',
      icon: ClipboardCheck,
      iconBgClass: 'bg-violet-100',
      iconColorClass: 'text-violet-600',
    },
    {
      title: 'Reviewed This Month',
      value: stats.reviewedThisMonth,
      subtitle: 'Completed reviews',
      icon: CheckCircle,
      iconBgClass: 'bg-green-100',
      iconColorClass: 'text-green-600',
    },
    {
      title: 'Avg Review Time',
      value: `${stats.avgReviewTime}d`,
      subtitle: 'Average turnaround',
      icon: TrendingUp,
      iconBgClass: 'bg-blue-100',
      iconColorClass: 'text-blue-600',
    },
    {
      title: 'Rejected',
      value: stats.rejectedAssessments,
      subtitle: 'Need revision',
      icon: AlertCircle,
      iconBgClass: 'bg-red-100',
      iconColorClass: 'text-red-600',
    },
  ]
})

const userStats = computed(() => {
  if (!statistics.data.value?.user) return []
  const stats = statistics.data.value.user
  return [
    {
      title: 'My Assessments',
      value: stats.myAssessments,
      subtitle: 'Assessments available for your organization',
      icon: FileText,
      iconBgClass: 'bg-blue-100',
      iconColorClass: 'text-blue-600',
    },
    {
      title: 'Completion Progress',
      value: `${stats.completionProgress}%`,
      subtitle: 'Your overall assessment progress',
      icon: TrendingUp,
      iconBgClass: 'bg-green-100',
      iconColorClass: 'text-green-600',
    },
    {
      title: 'Open Requirements',
      value: stats.openRequirements,
      subtitle: 'Requirements still being worked on',
      icon: AlertTriangle,
      iconBgClass: 'bg-amber-100',
      iconColorClass: 'text-amber-600',
    },
    {
      title: 'Organization Action Plans',
      value: stats.orgActionPlans,
      subtitle: 'Total action plans in organization',
      icon: UserCheck,
      iconBgClass: 'bg-violet-100',
      iconColorClass: 'text-violet-600',
    },
  ]
})

const statsToShow = computed(() => {
  if (isSuperAdmin) return superAdminStats.value
  if (isOrganizationAdmin) return orgAdminStats.value
  // For reviewer role, check if reviewer stats exist, otherwise fallback to user stats
  if (statistics.data.value?.reviewer && reviewerStats.value.length > 0) return reviewerStats.value
  return userStats.value
})

// ============================================================================
// Widget Visibility
// ============================================================================

const showStandardsList = computed(() => false) // Disabled for all users

const showOrganizationColumn = computed(() => isSuperAdmin)

// Chart always takes full width since standards list is removed
const chartsGridClass = computed(() => 'lg:grid-cols-1')

// ============================================================================
// Error Handling
// ============================================================================

const hasError = computed(() => 
  statistics.isError.value || 
  activities.isError.value || 
  assessments.isError.value
)

const errorMessage = computed(() => {
  const error = statistics.error.value || activities.error.value || assessments.error.value
  return error instanceof Error ? error.message : 'Failed to load dashboard data'
})
</script>

<template>
  <div class="space-y-6 p-6">
    <!-- Error Alert -->
    <Alert v-if="hasError" variant="destructive">
      <AlertCircle class="h-4 w-4" />
      <AlertTitle>Error</AlertTitle>
      <AlertDescription class="flex items-center gap-2">
        {{ errorMessage }}
        <Button
          variant="outline"
          size="sm"
          @click="statistics.refetch()"
        >
          Retry
        </Button>
      </AlertDescription>
    </Alert>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Show skeleton when statistics loading -->
      <template v-if="statistics.isLoading.value">
        <SkeletonCard v-for="i in 4" :key="i" />
      </template>

      <!-- Show data when ready -->
      <template v-else>
        <StatCard
          v-for="stat in statsToShow"
          :key="stat.title"
          :title="stat.title"
          :value="stat.value"
          :subtitle="stat.subtitle"
          :icon="stat.icon"
          :icon-bg-class="stat.iconBgClass"
          :icon-color-class="stat.iconColorClass"
        />
      </template>
    </div>

    <!-- Charts Row -->
    <div :class="['grid grid-cols-1 gap-6', chartsGridClass]">
      <AssessmentStatusChart
        :data="assessments.data.value?.byStatus"
        :loading="assessments.isLoading.value"
      />
      <StandardsList
        v-if="showStandardsList"
        :standards="standardsQuery.data.value"
        :loading="standardsQuery.isLoading.value"
      />
    </div>

    <!-- Middle Row: Activity & Action Plans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <RecentActivity 
        :activities="activities.data.value" 
        :loading="activities.isLoading.value" 
      />
      <ActionPlanList 
        :data="actionPlans.data.value" 
        :loading="actionPlans.isLoading.value" 
      />
    </div>

    <!-- Bottom Row: Recent Assessments & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <AssessmentTable 
          :assessments="assessments.data.value?.recent" 
          :loading="assessments.isLoading.value"
          :show-organization="showOrganizationColumn"
        />
      </div>
      <div>
        <QuickActions />
      </div>
    </div>
  </div>
</template>
