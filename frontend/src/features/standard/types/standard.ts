// Standard Types
// Based on backend models and frontend requirements

// ============================================================================
// Enums
// ============================================================================

/**
 * Standard Type Classification
 * Matches backend: internal, regulatory, standard, bestPractice, other
 */
export type StandardType =
  | "internal"
  | "regulatory"
  | "standard"
  | "bestPractice"
  | "other";

export type StandardSectionType = "domain" | "element" | "principle";

// ============================================================================
// Core Types
// ============================================================================

export interface Standard {
  id: string;
  name: string;
  version: string;
  type: StandardType;
  description?: string | null;
  isActive: boolean;
  periodType?: string | null;
  createdAt?: string;
  updatedAt?: string;
}

export interface StandardSection {
  id: string;
  standardId: string;
  parentId?: string | null;
  type?: string | null;
  code: string;
  title: string;
  description?: string | null;
  level: number;
  children?: StandardSection[];
  requirements?: StandardRequirement[];
  createdAt?: string;
  updatedAt?: string;
}

export interface StandardRequirement {
  id: string;
  standardSectionId: string;
  displayCode: string;
  type?: string | null;
  title: string;
  description?: string | null;
  evidenceHint?: string | null;
  createdAt?: string;
  updatedAt?: string;
}

// ============================================================================
// Request Types
// ============================================================================

export interface CreateStandardRequest {
  name: string;
  version: string;
  type?: StandardType;
  description?: string;
  isActive?: boolean;
  periodType?: string;
}

export interface UpdateStandardRequest {
  name?: string;
  version?: string;
  type?: StandardType;
  description?: string | null;
  isActive?: boolean;
  periodType?: string | null;
}

export interface CreateSectionRequest {
  standardId: string;
  parentId?: string | null;
  type?: string | null;
  code: string;
  title: string;
  description?: string | null;
  level?: number;
}

export interface UpdateSectionRequest {
  parentId?: string | null;
  type?: string | null;
  code?: string;
  title?: string;
  description?: string | null;
  level?: number;
}

export interface CreateRequirementRequest {
  standardSectionId: string;
  displayCode: string;
  title: string;
  description?: string | null;
  evidenceHint?: string | null;
}

export interface UpdateRequirementRequest {
  displayCode?: string;
  title?: string;
  description?: string | null;
  evidenceHint?: string | null;
}

// ============================================================================
// Filter and Pagination Types
// ============================================================================

export interface StandardFilters {
  type?: StandardType | "";
  isActive?: boolean;
  search?: string;
  perPage?: number;
  page?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
}

export interface SectionFilters {
  standardId: string;
  parentId?: string | null;
  type?: string | null;
}

export interface RequirementFilters {
  standardSectionId?: string;
  standardId?: string;
  search?: string;
}

// ============================================================================
// API Response Types
// ============================================================================

export interface PaginationMeta {
  currentPage: number;
  perPage: number;
  total: number;
  lastPage: number;
  from?: number;
  to?: number;
}

export interface StandardCollectionResponse {
  data: Standard[];
  meta: PaginationMeta;
}

export interface SectionCollectionResponse {
  data: StandardSection[];
}

export interface RequirementCollectionResponse {
  data: StandardRequirement[];
  meta: PaginationMeta;
}

export interface StandardResponse {
  data: Standard;
}

export interface SectionResponse {
  data: StandardSection;
}

export interface RequirementResponse {
  data: StandardRequirement;
}

// ============================================================================
// Organization Assessment Types
// ============================================================================

export interface OrganizationByStandard {
  id: string
  name: string
  hasAssessment: boolean
  assessmentId: string | null
  assessmentStatus: string | null
  assessmentProgress: number | null
  startedAt: string | null
}

// ============================================================================
// Standard Report Types
// ============================================================================

export interface StandardReport {
  id: string
  name: string
  type: StandardType
  version: string
  createdAt: string
  stats: {
    totalOrganizations: number
    startedOrganizations: number
    notStartedOrganizations: number
  }
}

export interface StandardReportFilters {
  search?: string
  perPage?: number
  page?: number
  sortBy?: string
  sortOrder?: "asc" | "desc"
}
