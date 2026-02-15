import { computed } from 'vue'
import { useUserStore } from '@/features/auth/stores/userStore'

/**
 * Composable for role-based access control
 * Provides reactive role checking utilities
 */
export function useRoles() {
  const userStore = useUserStore()

  /**
   * Get the user's roles array
   * Memoized for performance
   */
  const roles = computed(() => userStore.user?.roles || [])

  /**
   * Check if user has a specific role
   * @param role - The role string to check
   * @returns boolean - true if user has the role
   */
  function hasRole(role: string): boolean {
    if (!roles.value || roles.value.length === 0) {
      return false
    }
    return roles.value.includes(role)
  }

  /**
   * Check if user has any of the specified roles (OR logic)
   * @param roleList - Array of role strings to check
   * @returns boolean - true if user has at least one role
   */
  function hasAnyRole(roleList: string[]): boolean {
    if (!roles.value || roleList.length === 0) {
      return false
    }
    return roleList.some(role => roles.value.includes(role))
  }

  /**
   * Check if user has all specified roles (AND logic)
   * @param roleList - Array of role strings to check
   * @returns boolean - true if user has all roles
   */
  function hasAllRoles(roleList: string[]): boolean {
    if (!roles.value || roleList.length === 0) {
      return false
    }
    return roleList.every(role => roles.value.includes(role))
  }

  /**
   * Check if user lacks a specific role
   * @param role - The role string to check
   * @returns boolean - true if user does NOT have the role
   */
  function lacksRole(role: string): boolean {
    return !hasRole(role)
  }

  /**
   * Get list of missing roles from a given list
   * @param roleList - Array of role strings to check
   * @returns string[] - Array of roles the user does not have
   */
  function getMissingRoles(roleList: string[]): string[] {
    if (!roles.value || roleList.length === 0) {
      return []
    }
    return roleList.filter(role => !roles.value.includes(role))
  }

  /**
   * Check if user is authenticated
   */
  const isAuthenticated = computed(() => userStore.isAuthenticated)

  /**
   * Get current user
   */
  const user = computed(() => userStore.user)

  return {
    // Roles checks
    hasRole,
    hasAnyRole,
    hasAllRoles,
    lacksRole,
    getMissingRoles,
    
    // User state
    roles,
    isAuthenticated,
    user,
  }
}
