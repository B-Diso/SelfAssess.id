import { apiClient } from '@/lib/api/client'
import type { ApiDataResponse, ApiMessageResponse, PaginatedResponse } from '@/types/api.types'
import type { User } from '@/features/user/types/user.types'
import type {
  AdminUserFilters,
  CreateAdminUserRequest,
  UpdateAdminUserRequest,
} from '../types/adminUser.types'

export const adminUserApi = {
  async getUsers(filters?: AdminUserFilters): Promise<PaginatedResponse<User>> {
    const params = filters ? { ...filters } : undefined
    if (params && params.role === 'all') {
      delete params.role
    }
    if (params && params.organizationId === '') {
      delete params.organizationId
    }

    const response = await apiClient.get<PaginatedResponse<User>>('/admin/users', {
      params,
    })

    return response.data
  },

  async createUser(data: CreateAdminUserRequest): Promise<ApiDataResponse<User>> {
    const response = await apiClient.post<{ data: User; message?: string }>('/admin/users', data)

    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  async updateUser(id: string, data: UpdateAdminUserRequest): Promise<ApiDataResponse<User>> {
    const response = await apiClient.put<{ data: User; message?: string }>(`/admin/users/${id}`, data)

    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  async deleteUser(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(`/admin/users/${id}`)

    return {
      message: response.data?.message ?? 'User deleted successfully',
    }
  },
}
