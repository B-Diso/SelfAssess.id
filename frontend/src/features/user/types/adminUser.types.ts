import type { UserFilters } from '@/features/user/types/user.types'

export interface AdminUserFilters extends UserFilters {
  sortBy?: UserFilters['sortBy'] | 'organizationName'
}

export interface CreateAdminUserRequest {
  name: string
  email: string
  password: string
  role: string
  organizationId: string
}

export interface UpdateAdminUserRequest {
  name?: string
  email?: string
  password?: string
  role?: string
}

export interface AssignAdminRoleRequest {
  role: string
}
