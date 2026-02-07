export interface User {
  id: string
  organizationId: string
  organizationName: string
  organizationDescription: string | null
  organizationIsActive: boolean
  name: string
  email: string
  emailVerifiedAt: string | null
  roles: string[]
  permissions: string[]
  lastLoginAt: string | null
  createdAt: string
  updatedAt: string
}

export interface LoginRequest {
  email: string
  password: string
  rememberMe?: boolean
}

export interface LoginResponse {
  accessToken: string
  tokenType: string
  expiresIn: number
  user: User
  rememberMe: boolean
  sessionDuration: string
}

export interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
}
