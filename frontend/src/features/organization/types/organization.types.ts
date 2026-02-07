// Organization types based on API documentation

export interface Organization {
  id: string;
  name: string;
  description: string | null;
  isActive: boolean;
  userCount?: number; // Only for Super Admin
  activeUserCount?: number; // Only for Super Admin
  createdAt: string;
  updatedAt: string;
}

export interface OrganizationFilters {
  page?: number;
  perPage?: number;
  search?: string;
  isActive?: boolean | "all" | string;
  sortBy?: "name" | "created_at" | "updated_at";
  sortOrder?: "asc" | "desc";
}

export interface CreateOrganizationRequest {
  name: string;
  description?: string;
}

export interface UpdateOrganizationRequest {
  name?: string;
  description?: string;
  isActive?: boolean;
}

export interface OrganizationMember {
  id: string;
  name: string;
  email: string;
  roles: string[];
  emailVerifiedAt: string | null;
  lastLoginAt: string | null;
  createdAt: string;
  updatedAt: string;
}
