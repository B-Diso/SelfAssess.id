// Role types based on API documentation

export interface Role {
  id: number
  name: string
  guardName: string
  isSystem : boolean,
  permissions: string[]
  createdAt: string
  updatedAt: string
}

export interface Permission {
  id: number
  name: string
  guardName: string
  createdAt: string
  updatedAt: string
}

export interface CreateRoleRequest {
  name: string
  guardName?: string  // Optional: defaults to 'api' in backend
  permissions?: string[]
}

export interface UpdateRoleRequest {
  name?: string
  guardName?: string
  permissions?: string[]
}
