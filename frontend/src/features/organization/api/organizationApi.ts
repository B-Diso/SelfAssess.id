import { apiClient } from '@/lib/api/client'
import type { ApiDataResponse, ApiMessageResponse, PaginatedResponse } from '@/types/api.types'
import type {
  Organization,
  OrganizationFilters,
  CreateOrganizationRequest,
  UpdateOrganizationRequest,
  OrganizationMember,
} from '../types/organization.types'

export const organizationApi = {
  // GET /organizations - List organizations
  async getOrganizations(filters?: OrganizationFilters): Promise<PaginatedResponse<Organization>> {
    // Filter out undefined/null/empty/"all" values to avoid sending unnecessary params
    const params = filters ? { ...filters } : undefined
    if (params) {
      Object.keys(params).forEach(key => {
        const value = params[key as keyof OrganizationFilters]
        if (value === undefined || value === null || value === '' || value === 'all') {
          delete params[key as keyof OrganizationFilters]
        }
      })
    }

    const response = await apiClient.get<PaginatedResponse<Organization>>('/organizations', {
      params,
    })
    return response.data
  },

  // GET /organizations/{id} - Get organization detail
  async getOrganization(id: string): Promise<Organization> {
    const response = await apiClient.get<{ data: Organization }>(`/organizations/${id}`)
    return response.data.data
  },

  // POST /organizations - Create organization (Super Admin only)
  async createOrganization(data: CreateOrganizationRequest): Promise<ApiDataResponse<Organization>> {
    const response = await apiClient.post<{ data: Organization; message?: string }>('/organizations', data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // PUT /organizations/{id} - Update organization
  async updateOrganization(id: string, data: UpdateOrganizationRequest): Promise<ApiDataResponse<Organization>> {
    const response = await apiClient.put<{ data: Organization; message?: string }>(`/organizations/${id}`, data)
    return {
      data: response.data.data,
      message: response.data.message,
    }
  },

  // DELETE /organizations/{id} - Delete organization (Super Admin only)
  async deleteOrganization(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(`/organizations/${id}`)
    return {
      message: response.data?.message ?? 'Organization deleted successfully',
    }
  },

  // GET /organizations/{id}/users - Get organization members
  async getOrganizationMembers(id: string): Promise<OrganizationMember[]> {
    const response = await apiClient.get<{ data: OrganizationMember[] }>(`/organizations/${id}/users`)
    return response.data.data
  },
}
