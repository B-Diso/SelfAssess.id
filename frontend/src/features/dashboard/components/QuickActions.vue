<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { 
  Users, 
  Building2, 
  Shield, 
  UserCircle, 
  FilePlus, 
  ClipboardList,
  ArrowRight
} from 'lucide-vue-next'
import { useAuth } from '@/features/auth/composables/useAuth'
import { PERMISSIONS } from '@/lib/constants'

const { hasPermission, isSuperAdmin, isOrganizationAdmin } = useAuth()

// Check if user can create assessments (only Org Admin & Super Admin)
const canCreateAssessment = computed(() => isOrganizationAdmin || isSuperAdmin)

interface QuickAction {
  label: string
  description: string
  icon: typeof Users
  to: string
  visible: boolean
  variant: 'blue' | 'green' | 'purple' | 'gray' | 'amber'
}

const actions = computed<QuickAction[]>(() => [
  {
    label: 'Manage Users',
    description: 'Create, edit, or delete users',
    icon: Users,
    to: '/users',
    visible: hasPermission(PERMISSIONS.CREATE_USER),
    variant: 'blue',
  },
  {
    label: 'Manage Organizations',
    description: 'View and manage organizations',
    icon: Building2,
    to: '/organizations',
    visible: isSuperAdmin,
    variant: 'green',
  },
  {
    label: 'Manage Roles',
    description: 'Configure roles and permissions',
    icon: Shield,
    to: '/roles',
    visible: isSuperAdmin,
    variant: 'purple',
  },
  {
    label: 'Create Assessment',
    description: 'Start a new quality assessment',
    icon: FilePlus,
    to: '/assessments',
    visible: canCreateAssessment.value,
    variant: 'amber',
  },
  {
    label: 'View Assessments',
    description: 'Browse all assessments',
    icon: ClipboardList,
    to: '/assessments',
    visible: hasPermission(PERMISSIONS.VIEW_ASSESSMENTS),
    variant: 'blue',
  },
  {
    label: 'My Profile',
    description: 'View and edit your profile',
    icon: UserCircle,
    to: '/profile',
    visible: true,
    variant: 'gray',
  },
])

const visibleActions = computed(() => actions.value.filter(a => a.visible))

const getVariantBg = (variant: string): string => {
  const map: Record<string, string> = {
    blue: 'bg-blue-50 dark:bg-blue-950',
    green: 'bg-green-50 dark:bg-green-950',
    purple: 'bg-purple-50 dark:bg-purple-950',
    gray: 'bg-gray-50 dark:bg-gray-900',
    amber: 'bg-amber-50 dark:bg-amber-950',
  }
  return map[variant] ?? 'bg-gray-50 dark:bg-gray-900'
}

const getVariantHover = (variant: string): string => {
  const map: Record<string, string> = {
    blue: 'hover:bg-blue-100 dark:hover:bg-blue-900',
    green: 'hover:bg-green-100 dark:hover:bg-green-900',
    purple: 'hover:bg-purple-100 dark:hover:bg-purple-900',
    gray: 'hover:bg-gray-100 dark:hover:bg-gray-800',
    amber: 'hover:bg-amber-100 dark:hover:bg-amber-900',
  }
  return map[variant] ?? 'hover:bg-gray-100 dark:hover:bg-gray-800'
}

const getVariantText = (variant: string): string => {
  const map: Record<string, string> = {
    blue: 'text-blue-900 dark:text-blue-100',
    green: 'text-green-900 dark:text-green-100',
    purple: 'text-purple-900 dark:text-purple-100',
    gray: 'text-gray-900 dark:text-gray-100',
    amber: 'text-amber-900 dark:text-amber-100',
  }
  return map[variant] ?? 'text-gray-900 dark:text-gray-100'
}

const getVariantSubtext = (variant: string): string => {
  const map: Record<string, string> = {
    blue: 'text-blue-700 dark:text-blue-300',
    green: 'text-green-700 dark:text-green-300',
    purple: 'text-purple-700 dark:text-purple-300',
    gray: 'text-gray-700 dark:text-gray-300',
    amber: 'text-amber-700 dark:text-amber-300',
  }
  return map[variant] ?? 'text-gray-700 dark:text-gray-300'
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="text-lg font-semibold">Quick Actions</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-2">
        <router-link
          v-for="action in visibleActions"
          :key="action.label"
          :to="action.to"
          :class="[
            'block p-3 rounded-lg transition-colors group',
            getVariantBg(action.variant),
            getVariantHover(action.variant)
          ]"
        >
          <div class="flex items-start gap-3">
            <div :class="['p-2 rounded-md shrink-0', getVariantBg(action.variant)]">
              <component :is="action.icon" :class="['h-4 w-4', getVariantText(action.variant)]" />
            </div>
            <div class="flex-1 min-w-0">
              <p :class="['font-medium text-sm', getVariantText(action.variant)]">
                {{ action.label }}
              </p>
              <p :class="['text-xs mt-0.5', getVariantSubtext(action.variant)]">
                {{ action.description }}
              </p>
            </div>
            <ArrowRight :class="['h-4 w-4 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity', getVariantText(action.variant)]" />
          </div>
        </router-link>
      </div>
    </CardContent>
  </Card>
</template>
