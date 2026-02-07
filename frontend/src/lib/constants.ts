// Application constants

import { API_BASE_URL } from '../config/api';

export { API_BASE_URL };

export const APP_TITLE = import.meta.env.VITE_APP_TITLE || "SelfAssess.id";

export const ACCESS_TOKEN_KEY = "token";
export const ACCESS_TOKEN_EXPIRY_KEY = "token_expires_at";
export const TOKEN_REFRESH_THRESHOLD_MS = 60 * 1000;

export const ROLES = {
  SUPER_ADMIN: "super_admin",
  ORGANIZATION_ADMIN: "organization_admin",
  ORGANIZATION_USER: "organization_user",
} as const;

export const PERMISSIONS = {
  // User permissions
  CREATE_USER: "create-user",
  VIEW_USERS: "view-users",
  UPDATE_USER: "update-user",
  DELETE_USER: "delete-user",
  TRANSFER_USER: "transfer-user",
  ASSIGN_ROLES: "assign-roles",

  // Organization permissions
  CREATE_ORGANIZATION: "create-organization",
  VIEW_ORGANIZATIONS: "view-organizations",
  UPDATE_ORGANIZATION: "update-organization",
  DELETE_ORGANIZATION: "delete-organization",
  VIEW_ORGANIZATION_MEMBERS: "view-organization-members",

  // Role permissions
  MANAGE_ROLES: "manage-roles",
  VIEW_ROLES: "view-roles",
  VIEW_PERMISSIONS: "view-permissions",

  // Quality Assessment permissions
  REVIEW_QUALITY_ASSESSMENTS: "review-quality-assessments",
  VIEW_ASSESSMENTS: "view-assessments",
  VIEW_STANDARDS: "view-standards",
  VIEW_ATTACHMENTS: "view-attachments",
} as const;

export const PAGINATION = {
  DEFAULT_PAGE: 1,
  DEFAULT_PER_PAGE: 15,
  MAX_PER_PAGE: 100,
} as const;
