import { computed } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { authApi, type UpdateProfileRequest } from '../api/authApi'
import { useAuthStore } from '../stores/authStore'
import { useUserStore } from '../stores/userStore'
import { useRouter } from 'vue-router'
import type { LoginRequest } from '../types/auth.types'
import { storeToRefs } from 'pinia'

export function useAuth() {
  const authStore = useAuthStore()
  const userStore = useUserStore()
  const { accessToken, isAuthenticated } = storeToRefs(authStore)
  const { user, isLoading: isUserLoading } = storeToRefs(userStore)
  const router = useRouter()
  const queryClient = useQueryClient()

  // Login mutation
  const loginMutation = useMutation({
    mutationFn: (credentials: LoginRequest) => authApi.login(credentials),
    onSuccess: (data) => {
      // Store only authentication tokens
      authStore.setTokens(data.accessToken, data.expiresIn)
      
      // Set user data in userStore
      if (data.user) {
        userStore.updateUser(data.user)
      }
      
      queryClient.invalidateQueries()
      router.push('/dashboard')
    },
  })

  // Logout mutation
  const logoutMutation = useMutation({
    mutationFn: () => authApi.logout(),
    onSuccess: () => {
      authStore.logout()
      userStore.clearUser()
      queryClient.clear()
      router.push('/login')
    },
    onError: () => {
      // Even if API call fails, logout locally
      authStore.logout()
      userStore.clearUser()
      queryClient.clear()
      router.push('/login')
    },
  })

  // Get current user query - fetch when accessToken exists
  const currentUserQuery = useQuery({
    queryKey: ['auth', 'me'],
    queryFn: async () => {
      const userData = await authApi.getCurrentUser()
      // Update userStore with the fetched data
      userStore.updateUser(userData)
      return userData
    },
    enabled: computed(() => !!accessToken.value),
    staleTime: 5 * 60 * 1000, // 5 minutes
  })

  // Update profile mutation
  const updateProfileMutation = useMutation({
    mutationFn: (data: UpdateProfileRequest) => authApi.updateProfile(data),
    onSuccess: (response) => {
      if (response?.data) {
        // Update the userStore directly
        userStore.updateUser(response.data)
        // Update the query cache as well
        queryClient.setQueryData(['auth', 'me'], response.data)
        queryClient.invalidateQueries({ queryKey: ['auth', 'me'] })
      }
    },
  })

  return {
    // Queries
    currentUser: currentUserQuery,

    // Mutations
    login: loginMutation,
    logout: logoutMutation,
    updateProfile: updateProfileMutation,

    // Auth state from store
    isAuthenticated,
    accessToken,
    
    // User state from userStore
    user,
    isUserLoading,
    
    // User store methods
    hasPermission: userStore.hasPermission.bind(userStore),
    hasRole: userStore.hasRole.bind(userStore),
    hasAnyPermission: userStore.hasAnyPermission.bind(userStore),
    hasAllPermissions: userStore.hasAllPermissions.bind(userStore),
    hasAnyRole: userStore.hasAnyRole.bind(userStore),
    hasAllRoles: userStore.hasAllRoles.bind(userStore),
    
    // Computed role properties
    isSuperAdmin: userStore.isSuperAdmin,
    isOrganizationAdmin: userStore.isOrganizationAdmin,
    isOrganizationUser: userStore.isOrganizationUser,
  }
}
