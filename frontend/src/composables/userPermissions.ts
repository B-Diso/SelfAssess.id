import { computed } from 'vue'
import { useUserStore } from '@/features/auth/stores/userStore'

/**
 * Composable for permission-based access control
 * Provides reactive permission checking utilities
 */
export function usePermissions() {
  const userStore = useUserStore()

  /**
   * Get the user's permissions array
   * Memoized for performance
   */
  const permissions = computed(() => userStore.user?.permissions || [])

  /**
   * Check if user has a specific permission
   * @param permission - The permission string to check
   * @returns boolean - true if user has the permission
   */
  function hasPermission(permission: string): boolean {
    if (!permissions.value || permissions.value.length === 0) {
      return false
    }
    return permissions.value.includes(permission)
  }

  /**
   * Check if user has any of the specified permissions (OR logic)
   * @param permissionList - Array of permission strings to check
   * @returns boolean - true if user has at least one permission
   */
  function hasAnyPermission(permissionList: string[]): boolean {
    if (!permissions.value || permissionList.length === 0) {
      return false
    }
    return permissionList.some(permission => permissions.value.includes(permission))
  }

  /**
   * Check if user has all specified permissions (AND logic)
   * @param permissionList - Array of permission strings to check
   * @returns boolean - true if user has all permissions
   */
  function hasAllPermissions(permissionList: string[]): boolean {
    if (!permissions.value || permissionList.length === 0) {
      return false
    }
    return permissionList.every(permission => permissions.value.includes(permission))
  }

  /**
   * Check if user lacks a specific permission
   * @param permission - The permission string to check
   * @returns boolean - true if user does NOT have the permission
   */
  function lacksPermission(permission: string): boolean {
    return !hasPermission(permission)
  }

  /**
   * Get list of missing permissions from a given list
   * @param permissionList - Array of permission strings to check
   * @returns string[] - Array of permissions the user does not have
   */
  function getMissingPermissions(permissionList: string[]): string[] {
    if (!permissions.value || permissionList.length === 0) {
      return []
    }
    return permissionList.filter(permission => !permissions.value.includes(permission))
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
    // Permission checks
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    lacksPermission,
    getMissingPermissions,
    
    // User state
    permissions,
    isAuthenticated,
    user,
  }
}
