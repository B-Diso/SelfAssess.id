import type { RouteRecordRaw } from 'vue-router'
import { PERMISSIONS, ROLES } from '@/lib/constants'

export const userRoutes: RouteRecordRaw[] = [
  {
    path: '/users',
    name: 'UserList',
    component: () => import('@/features/user/pages/UserListPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_USERS,
      title: 'User Management',
      description: 'Manage users in your organization',
    },
  },
  {
    path: '/users/:id',
    name: 'UserDetail',
    component: () => import('@/features/user/pages/UserDetailPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_USERS,
      title: 'User Detail',
    },
  },
  {
    path: '/admin/users',
    name: 'AdminUserList',
    component: () => import('@/features/user/pages/AdminUserListPage.vue'),
    meta: {
      requiresAuth: true,
      permission: PERMISSIONS.VIEW_USERS,
      role: ROLES.SUPER_ADMIN,
      title: 'Global User Management',
      description: 'Manage users across every organization',
    },
  },
]
