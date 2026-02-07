import type { RouteRecordRaw } from "vue-router";
import { PERMISSIONS } from '@/lib/constants';

export const attachmentRoutes: RouteRecordRaw[] = [
  {
    path: "/attachments",
    name: "AttachmentList",
    component: () =>
      import("@/features/attachment/pages/AttachmentListPage.vue"),
    meta: {
      requiresAuth: true,
      title: "Attachment Library",
      permission: PERMISSIONS.VIEW_ATTACHMENTS,
    },
  },
];
