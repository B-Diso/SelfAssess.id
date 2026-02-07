import type { RouteRecordRaw } from 'vue-router'
import { PERMISSIONS } from '@/lib/constants'

export const organizationRoutes: RouteRecordRaw[] = [
  {
    path: '/organizations',
    name: 'OrganizationList',
    component: () => import('@/features/organization/pages/OrganizationListPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_ORGANIZATIONS,
    },
  },
  {
    path: '/organizations/:id',
    name: 'OrganizationDetail',
    component: () => import('@/features/organization/pages/OrganizationDetailPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_ORGANIZATIONS,
    },
  },
]
