import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { apiClient } from '@/lib/api/client'
import type { User } from '@/features/auth/types/auth.types'
import { useAuthStore } from './authStore'
import { ROLES } from '@/lib/constants'

// Define the user data storage key
const USER_DATA_KEY = 'user_data'

export const useUserStore = defineStore('user', () => {
  // State
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const isAuthenticated = computed(() => !!user.value)
  
  // Role-based computed properties
  const isSuperAdmin = computed(() =>
    user.value?.roles.includes(ROLES.SUPER_ADMIN) || false
  )
  
  const isOrganizationAdmin = computed(() =>
    user.value?.roles.includes(ROLES.ORGANIZATION_ADMIN) || false
  )
  
  const isOrganizationUser = computed(() =>
    user.value?.roles.includes(ROLES.ORGANIZATION_USER) || false
  )

  // Actions
  async function fetchUser() {
    const authStore = useAuthStore()
    
    // Only fetch user if authenticated
    if (!authStore.isAuthenticated) {
      clearUser()
      return
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await apiClient.get<User>('/auth/me')
      user.value = response.data
      
      // Persist to localStorage
      localStorage.setItem(USER_DATA_KEY, JSON.stringify(response.data))
    } catch (err) {
      console.error('Failed to fetch user data:', err)
      
      // Clear user data on authentication failure
      clearUser()
      
      // Set error message
      if (err instanceof Error) {
        error.value = err.message
      } else {
        error.value = 'Failed to fetch user data'
      }
      
      // If it's an authentication error, logout from auth store as well
      if (err && typeof err === 'object' && 'response' in err &&
          err.response && typeof err.response === 'object' &&
          'status' in err.response && err.response.status === 401) {
        authStore.logout()
      }
    } finally {
      isLoading.value = false
    }
  }

  function clearUser() {
    user.value = null
    localStorage.removeItem(USER_DATA_KEY)
  }

  function updateUser(userData: Partial<User> | User) {
    if (user.value) {
      user.value = { ...user.value, ...userData }
    } else {
      user.value = userData as User
    }
    localStorage.setItem(USER_DATA_KEY, JSON.stringify(user.value))
  }

  function hasPermission(permission: string): boolean {
    return user.value?.permissions.includes(permission) || false
  }

  function hasRole(role: string): boolean {
    return user.value?.roles.includes(role) || false
  }

  function hasAnyPermission(permissions: string[]): boolean {
    if (!user.value || permissions.length === 0) return false
    return permissions.some(permission => user.value!.permissions.includes(permission))
  }

  function hasAllPermissions(permissions: string[]): boolean {
    if (!user.value || permissions.length === 0) return false
    return permissions.every(permission => user.value!.permissions.includes(permission))
  }

  function hasAnyRole(roles: string[]): boolean {
    if (!user.value || roles.length === 0) return false
    return roles.some(role => user.value!.roles.includes(role))
  }

  function hasAllRoles(roles: string[]): boolean {
    if (!user.value || roles.length === 0) return false
    return roles.every(role => user.value!.roles.includes(role))
  }

  // Rehydrate user data from localStorage
  function rehydrate() {
    const storedUserData = localStorage.getItem(USER_DATA_KEY)
    
    if (storedUserData) {
      try {
        user.value = JSON.parse(storedUserData)
      } catch (err) {
        console.error('Failed to parse stored user data:', err)
        localStorage.removeItem(USER_DATA_KEY)
      }
    }
  }

  // Initialize user data if token exists in authStore
  async function initialize() {
    const authStore = useAuthStore()
    
    // First rehydrate from localStorage
    rehydrate()
    
    // If authenticated and no user data, fetch it
    if (authStore.isAuthenticated && !user.value) {
      await fetchUser()
    }
  }

  // Watch for auth changes to automatically fetch/clear user data
  const authStore = useAuthStore()
  watch(
    () => authStore.isAuthenticated,
    async (isAuthenticated) => {
      if (isAuthenticated) {
        await fetchUser()
      } else {
        clearUser()
      }
    },
    { immediate: false }
  )

  // Initialize on store creation
  initialize()

  return {
    // State
    user,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    isSuperAdmin,
    isOrganizationAdmin,
    isOrganizationUser,
    
    // Actions
    fetchUser,
    clearUser,
    updateUser,
    hasPermission,
    hasRole,
    hasAnyPermission,
    hasAllPermissions,
    hasAnyRole,
    hasAllRoles,
    rehydrate,
    initialize,
  }
})