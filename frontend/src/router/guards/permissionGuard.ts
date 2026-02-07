import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/authStore'
import { useUserStore } from '@/features/auth/stores/userStore'

export function permissionGuard(
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext
) {
  const authStore = useAuthStore()
  const userStore = useUserStore()

  // Only proceed if user is authenticated (authGuard should handle this)
  if (!authStore.isAuthenticated) {
    next()
    return
  }

  // Check if user data is loaded
  if (!userStore.user && !userStore.isLoading) {
    // If user data is not loaded and not currently loading, fetch it
    userStore.fetchUser().then(() => {
      // Continue with navigation after fetching user data
      next()
    }).catch(() => {
      // If fetching user data fails, continue anyway (authGuard will handle if needed)
      next()
    })
    return
  }

  // If user data is still loading, we need to wait
  if (userStore.isLoading) {
    // Create a watcher to wait for loading to complete
    const unwatch = userStore.$subscribe(() => {
      if (!userStore.isLoading) {
        unwatch()
        // Continue with navigation after loading is complete
        next()
      }
    })
    return
  }

  // Check if route requires specific permission
  const requiredPermission = to.meta.permission as string | undefined

  if (requiredPermission && !userStore.hasPermission(requiredPermission)) {
    // User doesn't have required permission
    console.warn(`Access denied: Missing permission "${requiredPermission}"`)
    next('/dashboard') // Redirect to dashboard
    return
  }

  // Check if route requires specific role
  const requiredRole = to.meta.role as string | undefined

  if (requiredRole && !userStore.hasRole(requiredRole)) {
    // User doesn't have required role
    console.warn(`Access denied: Missing role "${requiredRole}"`)
    next('/dashboard') // Redirect to dashboard
    return
  }

  next()
}
