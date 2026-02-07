import { apiClient } from '@/lib/api/client'
import type { LoginRequest, LoginResponse, User } from '../types/auth.types'

export interface UpdateProfileRequest {
  name?: string
  email?: string
}

export interface UpdatePasswordRequest {
  currentPassword: string
  password: string
  passwordConfirmation: string
}

export interface ProfileResponse {
  message: string
  data: User
}

export const authApi = {
  async login(credentials: LoginRequest): Promise<LoginResponse> {
    const response = await apiClient.post<LoginResponse>('/auth/login', credentials, {
      withCredentials: true,
    })
    return response.data
  },

  async logout(): Promise<void> {
    await apiClient.post('/auth/logout')
  },

  async getCurrentUser(): Promise<User> {
    const response = await apiClient.get<User>('/auth/me')
    return response.data
  },

  async refreshToken(): Promise<{ accessToken: string; expiresIn: number }> {
    const response = await apiClient.post<{ accessToken: string; expiresIn: number }>('/auth/refresh', null, {
      withCredentials: true,
    })
    return response.data
  },

  async updateProfile(data: UpdateProfileRequest): Promise<ProfileResponse> {
    const response = await apiClient.patch<ProfileResponse>('/me/profile', data)
    return response.data
  },

  async updatePassword(data: UpdatePasswordRequest): Promise<{ message: string }> {
    const response = await apiClient.patch<{ message: string }>('/me/password', data)
    return response.data
  },
}
