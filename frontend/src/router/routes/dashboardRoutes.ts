import type { RouteRecordRaw } from 'vue-router'

export const dashboardRoutes: RouteRecordRaw[] = [
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/features/dashboard/pages/DashboardPage.vue'),
    meta: {
      requiresAuth: true,
    },
  },
]
