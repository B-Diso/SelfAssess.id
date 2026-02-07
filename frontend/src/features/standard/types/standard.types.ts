// Domain Types
export interface StandardDomain {
  id: string
  code: string
  title: string
  description?: string
  position: number
  isActive: boolean
  elementsCount?: number
  createdAt?: string
  updatedAt?: string
}

export interface CreateDomainRequest {
  standard_id: string
  code: string
  title: string
  description?: string
  position?: number
  is_active?: boolean
}

export interface UpdateDomainRequest {
  code?: string
  title?: string
  description?: string
  position?: number
  is_active?: boolean
}

export interface DomainListParams {
  search?: string
  isActive?: boolean
  sortBy?: string
  sortOrder?: 'asc' | 'desc'
  page?: number
  perPage?: number
}

// Element Types
export interface StandardElement {
  id: string
  standardDomainId: string
  standardDomainName?: string
  standardDomainCode?: string
  code: string
  title: string
  description?: string
  position: number
  isActive: boolean
  requirementsCount?: number
  domain?: StandardDomain
  createdAt?: string
  updatedAt?: string
}

export interface CreateElementRequest {
  standard_domain_id: string
  code: string
  title: string
  description?: string
  position?: number
  is_active?: boolean
}

export interface UpdateElementRequest {
  standard_domain_id?: string
  code?: string
  title?: string
  description?: string
  position?: number
  is_active?: boolean
}

export interface ElementListParams {
  standardDomainId?: string
  search?: string
  isActive?: boolean
  sortBy?: string
  sortOrder?: 'asc' | 'desc'
  page?: number
  perPage?: number
}

// Requirement Types

export type RiskLevel = 'low' | 'medium' | 'high' | 'critical'

export interface StandardRequirement {
  id: string
  standardElementId: string
  standardDomainId?: string
  masterRequirementId: string | null
  standardElementName?: string
  standardDomainName?: string
  standardElementCode?: string
  code: string
  title: string
  description?: string
  evidenceHint?: string
  weight?: number
  isMandatory: boolean
  riskLevel: RiskLevel
  copiedFromMasterAt?: string | null
  position: number
  isActive: boolean
  element?: StandardElement
  createdAt?: string
  updatedAt?: string
}

export interface CreateRequirementRequest {
  standard_element_id: string
  code: string
  title: string
  description?: string
  evidence_hint?: string
  weight?: number
  is_mandatory?: boolean
  risk_level?: RiskLevel
  position?: number
  is_active?: boolean
}

export interface UpdateRequirementRequest {
  standard_element_id?: string
  code?: string
  title?: string
  description?: string
  evidence_hint?: string
  weight?: number
  is_mandatory?: boolean
  risk_level?: RiskLevel
  position?: number
  is_active?: boolean
}

export interface RequirementListParams {
  standardElementId?: string
  search?: string
  isActive?: boolean
  sortBy?: string
  sortOrder?: 'asc' | 'desc'
  page?: number
  perPage?: number
}

export interface Standard {
  id: string
  name: string
  version: string
  type: string
  description?: string
  isActive: boolean
}

export interface StandardSection {
  id: string
  standardId: string
  parentId?: string
  type: 'domain' | 'element' | 'principle'
  code: string
  title: string
  description?: string
  order: number
  children?: StandardSection[]
  requirements?: StandardRequirement[]
}

