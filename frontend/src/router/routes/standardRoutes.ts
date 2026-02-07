import type { RouteRecordRaw } from "vue-router";
import { PERMISSIONS, ROLES } from '@/lib/constants';

export const standardRoutes: RouteRecordRaw[] = [
  {
    path: "/standards",
    name: "standards.list",
    component: () => import("@/features/standard/pages/ListPage.vue"),
    meta: {
      requiresAuth: true,
      title: "Standards",
      permission: PERMISSIONS.VIEW_STANDARDS,
    },
  },
  {
    path: "/standards/report",
    name: "standards.report",
    component: () => import("@/features/standard/pages/StandardReportPage.vue"),
    meta: {
      requiresAuth: true,
      title: "Standard Report",
      role: ROLES.SUPER_ADMIN,
    },
  },
  {
    path: "/standards/create",
    name: "standards.create",
    component: () =>
      import("@/features/standard/pages/management/WorkspacePage.vue"),
    meta: {
      requiresAuth: true,
      title: "Create Standard",
      role: ROLES.SUPER_ADMIN, // Only Super Admin can create standards
    },
  },
  {
    path: "/standards/:id",
    name: "standards.show",
    component: () =>
      import("@/features/standard/pages/management/WorkspacePage.vue"),
    meta: {
      requiresAuth: true,
      title: "Standard Details",
      role: ROLES.SUPER_ADMIN, // WorkspacePage is for management, Super Admin only
    },
  },
  {
    path: "/standards/:id/organizations",
    name: "standards.organizations",
    component: () =>
      import("@/features/standard/pages/StandardOrganizationsPage.vue"),
    meta: {
      requiresAuth: true,
      title: "Standard Organizations",
      role: ROLES.SUPER_ADMIN, // Only Super Admin can access
    },
  },
];
