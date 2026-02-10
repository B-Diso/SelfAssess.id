/**
 * Standard API Service
 *
 * Provides API methods for managing Standards, Sections, and Requirements
 * following the existing apiClient pattern from adminUserApi.ts
 */

import { apiClient } from "@/lib/api/client";
import type {
  ApiDataResponse,
  ApiMessageResponse,
  PaginatedResponse,
} from "@/types/api.types";
import type {
  Standard,
  StandardSection,
  StandardRequirement,
  StandardReport,
  CreateStandardRequest,
  UpdateStandardRequest,
  CreateSectionRequest,
  UpdateSectionRequest,
  CreateRequirementRequest,
  UpdateRequirementRequest,
  StandardFilters,
  StandardReportFilters,
  OrganizationByStandard,
} from "../types/standard";

// ============================================================================
// Standard API Methods
// ============================================================================

export const standardApi = {
  // -------------------------
  // Standard CRUD Operations
  // -------------------------

  /**
   * GET /api/standards - List all standards with filtering
   */
  async getStandards(
    filters?: StandardFilters,
  ): Promise<PaginatedResponse<Standard>> {
    const params = filters
      ? {
          type: filters.type === "" || filters.type === "all" ? undefined : filters.type,
          isActive: filters.isActive,
          search: filters.search,
          perPage: filters.perPage,
          page: filters.page,
          sortBy: filters.sortBy,
          sortOrder: filters.sortOrder,
        }
      : undefined;

    const response = await apiClient.get<PaginatedResponse<Standard>>(
      "/standards",
      {
        params,
      },
    );

    return response.data;
  },

  /**
   * GET /api/standards/{id} - Get single standard details
   */
  async getStandard(id: string): Promise<ApiDataResponse<Standard>> {
    const response = await apiClient.get<{ data: Standard; message?: string }>(
      `/standards/${id}`,
    );

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * POST /api/standards - Create new standard
   */
  async createStandard(
    data: CreateStandardRequest,
  ): Promise<ApiDataResponse<Standard>> {
    const response = await apiClient.post<{ data: Standard; message?: string }>(
      "/standards",
      {
        name: data.name,
        version: data.version,
        type: data.type,
        description: data.description,
        isActive: data.isActive,
      },
    );

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * PUT /api/standards/{id} - Update standard
   */
  async updateStandard(
    id: string,
    data: UpdateStandardRequest,
  ): Promise<ApiDataResponse<Standard>> {
    const response = await apiClient.put<{ data: Standard; message?: string }>(
      `/standards/${id}`,
      {
        name: data.name,
        version: data.version,
        type: data.type,
        description: data.description,
        isActive: data.isActive,
      },
    );

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * DELETE /api/standards/{id} - Delete standard
   */
  async deleteStandard(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/standards/${id}`,
    );

    return {
      message: response.data?.message ?? "Standard deleted successfully",
    };
  },

  /**
   * GET /api/standards/{id}/tree - Get full standard tree (flat list)
   */
  async getStandardTree(id: string): Promise<any[]> {
    const response = await apiClient.get<{ data: any[] }>(
      `/standards/${id}/tree`,
    );
    return response.data.data;
  },

  /**
   * GET /api/reports/standards/{id}/organizations - Get organizations using this standard
   */
  async getStandardOrganizations(
    id: string,
    filters?: {
      page?: number
      perPage?: number
      search?: string
      sortBy?: string
      sortOrder?: string
    }
  ): Promise<PaginatedResponse<OrganizationByStandard>> {
    const response = await apiClient.get<PaginatedResponse<OrganizationByStandard>>(
      `/reports/standards/${id}/organizations`,
      {
        params: {
          page: filters?.page,
          per_page: filters?.perPage,
          search: filters?.search,
          sort_by: filters?.sortBy,
          sort_order: filters?.sortOrder,
        },
      }
    );
    return response.data;
  },

  /**
   * GET /api/reports/standards - Get standard report
   */
  async getStandardReport(
    filters?: StandardReportFilters,
  ): Promise<PaginatedResponse<StandardReport>> {
    const params = filters
      ? {
          search: filters.search,
          perPage: filters.perPage,
          page: filters.page,
          sortBy: filters.sortBy,
          sortOrder: filters.sortOrder,
        }
      : undefined;

    const response = await apiClient.get<PaginatedResponse<StandardReport>>(
      "/reports/standards",
      {
        params,
      },
    );

    return response.data;
  },

  // -------------------------
  // Section Operations (backward compatibility)
  // -------------------------

  /**
   * GET /api/standards/{standardId}/sections - Get sections for a standard (flat list)
   * @deprecated Use sectionApi.getSections instead
   */
  async getSections(standardId: string): Promise<StandardSection[]> {
    const response = await apiClient.get<PaginatedResponse<StandardSection>>(
      `/standards/${standardId}/sections`,
    );

    return response.data.data;
  },

  /**
   * POST /api/standards/{standardId}/sections - Create new section
   * @deprecated Use sectionApi.createSection instead
   */
  async createSection(
    standardId: string,
    data: CreateSectionRequest,
  ): Promise<ApiDataResponse<StandardSection>> {
    const response = await apiClient.post<{
      data: StandardSection;
      message?: string;
    }>(`/standards/${standardId}/sections`, data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },
};

// ============================================================================
// Section API Methods
// ============================================================================

export const sectionApi = {
  /**
   * GET /api/standards/{standardId}/sections - Get sections for a standard (flat list)
   */
  async getSections(
    standardId: string,
  ): Promise<PaginatedResponse<StandardSection>> {
    const response = await apiClient.get<PaginatedResponse<StandardSection>>(
      `/standards/${standardId}/sections`,
    );

    return response.data;
  },

  /**
   * GET /api/sections/{id} - Get single section details
   */
  async getSection(id: string): Promise<ApiDataResponse<StandardSection>> {
    const response = await apiClient.get<{
      data: StandardSection;
      message?: string;
    }>(`/sections/${id}`);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * POST /api/standards/{standardId}/sections - Create new section
   */
  async createSection(
    standardId: string,
    data: CreateSectionRequest,
  ): Promise<ApiDataResponse<StandardSection>> {
    const response = await apiClient.post<{
      data: StandardSection;
      message?: string;
    }>(`/standards/${standardId}/sections`, data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * POST /api/sections - Create section directly
   */
  async createSectionDirect(
    data: CreateSectionRequest,
  ): Promise<ApiDataResponse<StandardSection>> {
    const response = await apiClient.post<{
      data: StandardSection;
      message?: string;
    }>("/sections", data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * PUT /api/sections/{id} - Update section
   */
  async updateSection(
    id: string,
    data: UpdateSectionRequest,
  ): Promise<ApiDataResponse<StandardSection>> {
    const response = await apiClient.put<{
      data: StandardSection;
      message?: string;
    }>(`/sections/${id}`, data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * DELETE /api/sections/{id} - Delete section
   */
  async deleteSection(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/sections/${id}`,
    );

    return {
      message: response.data?.message ?? "Section deleted successfully",
    };
  },
};

// ============================================================================
// Requirement API Methods
// ============================================================================

export const requirementApi = {
  /**
   * GET /api/standards/requirements - List requirements with optional filters
   */
  async getRequirements(params?: {
    standardSectionId?: string;
    standardId?: string;
    search?: string;
    page?: number;
    perPage?: number;
    sortBy?: string;
    sortOrder?: "asc" | "desc";
  }): Promise<PaginatedResponse<StandardRequirement>> {
    const response = await apiClient.get<
      PaginatedResponse<StandardRequirement>
    >("/standards/requirements", { params });

    return response.data;
  },

  /**
   * GET /api/standards/{standardId}/requirements - Get requirements for a standard
   */
  async getRequirementsByStandard(
    standardId: string,
  ): Promise<PaginatedResponse<StandardRequirement>> {
    const response = await apiClient.get<
      PaginatedResponse<StandardRequirement>
    >(`/standards/${standardId}/requirements`);

    return response.data;
  },

  /**
   * GET /api/requirements/{id} - Get single requirement details
   */
  async getRequirement(
    id: string,
  ): Promise<ApiDataResponse<StandardRequirement>> {
    const response = await apiClient.get<{
      data: StandardRequirement;
      message?: string;
    }>(`/requirements/${id}`);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * POST /api/standards/{standardId}/requirements - Create new requirement
   */
  async createRequirement(
    standardId: string,
    data: CreateRequirementRequest,
  ): Promise<ApiDataResponse<StandardRequirement>> {
    const response = await apiClient.post<{
      data: StandardRequirement;
      message?: string;
    }>(`/standards/${standardId}/requirements`, data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * POST /api/requirements - Create requirement directly
   */
  async createRequirementDirect(
    data: CreateRequirementRequest,
  ): Promise<ApiDataResponse<StandardRequirement>> {
    const response = await apiClient.post<{
      data: StandardRequirement;
      message?: string;
    }>("/requirements", data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * PUT /api/requirements/{id} - Update requirement
   */
  async updateRequirement(
    id: string,
    data: UpdateRequirementRequest,
  ): Promise<ApiDataResponse<StandardRequirement>> {
    const response = await apiClient.put<{
      data: StandardRequirement;
      message?: string;
    }>(`/requirements/${id}`, data);

    return {
      data: response.data.data,
      message: response.data.message,
    };
  },

  /**
   * DELETE /api/requirements/{id} - Delete requirement
   */
  async deleteRequirement(id: string): Promise<ApiMessageResponse> {
    const response = await apiClient.delete<{ message?: string }>(
      `/requirements/${id}`,
    );

    return {
      message: response.data?.message ?? "Requirement deleted successfully",
    };
  },
};

// ============================================================================
// Combined Export
// ============================================================================

export const standardService = {
  standard: standardApi,
  section: sectionApi,
  requirement: requirementApi,
};

export default standardService;
