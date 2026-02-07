// Generic API response types

export interface ApiResponse<T = any> {
  data: T
  meta?: PaginationMeta
  message?: string
}

export interface ApiDataResponse<T = any> {
  data: T
  message?: string
}

export interface ApiMessageResponse {
  message: string
}

export interface PaginationMeta {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
  from?: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export interface PaginationParams {
  page?: number
  perPage?: number
  sortBy?: string
  sortOrder?: 'asc' | 'desc'
  search?: string
}
