import type { RouteRecordRaw } from 'vue-router'
import { PERMISSIONS } from '@/lib/constants'

export const assessmentRoutes: RouteRecordRaw[] = [
  {
    path: '/assessments',
    name: 'assessments.list',
    component: () => import('@/features/assessment/pages/AssessmentListPage.vue'),
    meta: { requiresAuth: true, title: 'Assessments', permission: PERMISSIONS.VIEW_ASSESSMENTS },
  },
  {
    path: '/assessments/:id',
    name: 'assessments.show',
    component: () => import('@/features/assessment/pages/AssessmentDetailPage.vue'),
    meta: { requiresAuth: true, title: 'Assessment Details', permission: PERMISSIONS.VIEW_ASSESSMENTS },
  },
  // Redirect old self-assessment route to new assessment detail route
  {
    path: '/assessments/:id/self-assessment',
    redirect: (to) => ({ name: 'assessments.show', params: { id: to.params.id } }),
  },
]
