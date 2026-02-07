import type { RouteRecordRaw } from 'vue-router'

export const authRoutes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/features/auth/pages/LoginPage.vue'),
    meta: {
      requiresAuth: false,
    },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/features/auth/pages/ProfilePage.vue'),
    meta: {
      requiresAuth: true,
    },
  },
]
