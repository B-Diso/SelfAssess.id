<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { useAuth } from '@/features/auth/composables/useAuth'

const { user, isSuperAdmin, isOrganizationAdmin } = useAuth()

const userName = computed(() => user.value?.name ?? 'User')
const organizationName = computed(() => user.value?.organizationName ?? '')

const roleLabel = computed(() => {
  const roles = user.value?.roles
  if (!roles?.length) return 'User'
  const role = roles[0]
  return role?.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()) ?? 'User'
})

const welcomeMessage = computed(() => {
  if (isSuperAdmin) {
    return 'You have full system access. Monitor organizations, manage users, and review system-wide assessments.'
  }
  if (isOrganizationAdmin) {
    return 'Create and manage assessments, oversee users, and monitor compliance activities from your dashboard.'
  }
  return 'Complete assessment requirements, upload evidence, and track your organization\'s compliance progress.'
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 17) return 'Good afternoon'
  return 'Good evening'
})
</script>

<template>
  <Card class="bg-gradient-to-r from-blue-600 to-blue-700 border-0">
    <CardContent class="p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="text-white">
          <p class="text-blue-100 text-sm font-medium">{{ greeting }}</p>
          <h2 class="text-2xl font-bold">Welcome back, {{ userName }}!</h2>
          <p class="mt-2 text-blue-100 max-w-2xl">{{ welcomeMessage }}</p>
          <div class="mt-4 flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/50 text-white">
              {{ roleLabel }}
            </span>
            <span v-if="organizationName" class="text-sm text-blue-100">
              • {{ organizationName }}
            </span>
          </div>
        </div>
        <div class="hidden md:block">
          <div class="h-16 w-16 rounded-full bg-white/10 flex items-center justify-center">
            <span class="text-3xl font-bold text-white">{{ userName.charAt(0).toUpperCase() }}</span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
