import type { RouteRecordRaw } from 'vue-router'
import { PERMISSIONS, ROLES } from '@/lib/constants'

export const roleRoutes: RouteRecordRaw[] = [
  {
    path: '/roles',
    name: 'RoleList',
    component: () => import('@/features/role/pages/RoleListPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_ROLES,
      role: ROLES.SUPER_ADMIN, // Only super admin can manage roles
    },
  },
  {
    path: '/roles/:id',
    name: 'RoleDetail',
    component: () => import('@/features/role/pages/RoleDetailPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_ROLES,
      role: ROLES.SUPER_ADMIN,
    },
  },
]
