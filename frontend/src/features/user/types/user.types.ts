// User types based on API documentation

export interface User {
  id: string;
  organizationId: string;
  organizationName: string;
  organizationDescription: string | null;
  organizationIsActive: boolean;
  isMasterOrganization?: boolean;
  name: string;
  email: string;
  emailVerifiedAt: string | null;
  roles: string[];
  permissions: string[];
  lastLoginAt: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface UserFilters {
  page?: number;
  perPage?: number;
  search?: string;
  organizationId?: string;
  role?: string;
  sortBy?: "name" | "email" | "createdAt" | "updatedAt" | "organizationName";
  sortOrder?: "asc" | "desc";
}

export interface CreateUserRequest {
  name: string;
  email: string;
  password: string;
  role: string;
}

export interface UpdateUserRequest {
  name?: string;
  email?: string;
  password?: string;
}

export interface AssignRoleRequest {
  role: string;
}
