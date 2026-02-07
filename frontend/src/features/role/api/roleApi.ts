import { apiClient } from '@/lib/api/client'
import type { ApiDataResponse, ApiMessageResponse } from '@/types/api.types'
import type {
  Role,
  Permission,
  CreateRoleRequest,
  UpdateRoleRequest,
} from '../types/role.types'

export const roleApi = {
  // GET /admin/roles - List all roles
  async getRoles(): Promise<Role[]> {
    const response = await apiClient.get<{ data: Role[] }>('/admin/roles')
    return response.data.data
  },

  // GET /admin/roles/{id} - Get role detail
  async getRole(id: number): Promise<Role> {
    const response = await apiClient.get<{ data: Role }>(`/admin/roles/${id}`)
    return response.data.data
  },

  // POST /admin/roles - Create custom role
  async createRole(data: CreateRoleRequest): Promise<ApiDataResponse<Role>> {
    const response = await apiClient.post<{ data: Role; message?: string }>('/admin/roles', data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // PUT /admin/roles/{id} - Update role
  async updateRole(id: number, data: UpdateRoleRequest): Promise<ApiDataResponse<Role>> {
    const response = await apiClient.put<{ data: Role; message?: string }>(`/admin/roles/${id}`, data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // DELETE /admin/roles/{id} - Delete custom role
  async deleteRole(id: number): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(`/admin/roles/${id}`)
    return {
      message: response.data?.message ?? 'Role deleted successfully',
    }
  },

  // GET /admin/permissions - List all permissions
  async getPermissions(): Promise<Permission[]> {
    const response = await apiClient.get<{ data: Permission[] }>('/admin/permissions')
    return response.data.data
  },
}
