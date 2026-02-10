import { apiClient } from '@/lib/api/client'
import type { ApiDataResponse, ApiMessageResponse, PaginatedResponse } from '@/types/api.types'
import type {
  User,
  UserFilters,
  CreateUserRequest,
  UpdateUserRequest,
  AssignRoleRequest,
} from '../types/user.types'

export const userApi = {
  // GET /users - List users in organization
  async getUsers(filters?: UserFilters): Promise<PaginatedResponse<User>> {
    // Transform filters: remove role='all' (means no filter)
    const params = filters ? { ...filters } : undefined
    if (params && params.role === 'all') {
      delete params.role
    }
    if (params && params.organizationId === 'all') {
      delete params.organizationId
    }
    const response = await apiClient.get<PaginatedResponse<User>>('/users', {
      params,
    })
    return response.data
  },

  // GET /users/{id} - Get user detail
  async getUser(id: string): Promise<User> {
    const response = await apiClient.get<{ data: User }>(`/users/${id}`)
    return response.data.data
  },

  // POST /users - Create user
  async createUser(data: CreateUserRequest): Promise<ApiDataResponse<User>> {
    const response = await apiClient.post<{ data: User; message?: string }>('/users', data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // PUT /users/{id} - Update user
  async updateUser(id: string, data: UpdateUserRequest): Promise<ApiDataResponse<User>> {
    const response = await apiClient.put<{ data: User; message?: string }>(`/users/${id}`, data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // DELETE /users/{id} - Delete user
  async deleteUser(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(`/users/${id}`)
    return {
      message: response.data?.message ?? 'User deleted successfully',
    }
  },

  // POST /users/{id}/roles - Assign role to user
  async assignRole(id: string, data: AssignRoleRequest): Promise<ApiDataResponse<User>> {
    const response = await apiClient.post<{ data: User; message?: string }>(`/users/${id}/roles`, data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // PUT /users/{id}/roles - Update user role
  async updateRole(id: string, data: AssignRoleRequest): Promise<ApiDataResponse<User>> {
    const response = await apiClient.put<{ data: User; message?: string }>(`/users/${id}/roles`, data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },
}
