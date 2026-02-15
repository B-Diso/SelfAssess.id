import { createRouter, createWebHistory } from "vue-router";
import type { NavigationGuardNext, RouteLocationNormalized } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/authStore";
import { useUserStore } from "@/features/auth/stores/userStore";
import { authRoutes } from "./routes/authRoutes";
import { dashboardRoutes } from "./routes/dashboardRoutes";
import { userRoutes } from "./routes/userRoutes";
import { organizationRoutes } from "./routes/organizationRoutes";
import { roleRoutes } from "./routes/roleRoutes";
import { assessmentRoutes } from "./routes/assessmentRoutes";
import { standardRoutes } from "./routes/standardRoutes";
import { attachmentRoutes } from "./routes/attachmentRoutes";

const routes = [
  {
    path: "/",
    redirect: "/dashboard",
  },
  ...authRoutes,
  ...dashboardRoutes,
  ...userRoutes,
  ...organizationRoutes,
  ...roleRoutes,
  ...assessmentRoutes,
  ...standardRoutes,
  ...attachmentRoutes,
  {
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    redirect: "/dashboard",
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Apply combined global guard
router.beforeEach(
  (
    to: RouteLocationNormalized,
    _from: RouteLocationNormalized,
    next: NavigationGuardNext,
  ) => {
    // First check authentication
    const authStore = useAuthStore();

    // If route requires auth and user is not authenticated
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      next({
        path: "/login",
        query: { redirect: to.fullPath },
      });
      return;
    }

    // If user is authenticated and trying to access login page
    if (to.path === "/login" && authStore.isAuthenticated) {
      next("/dashboard");
      return;
    }

    // If authenticated, check permissions
    if (authStore.isAuthenticated) {
      const userStore = useUserStore();

      // Check if user data is loaded
      if (!userStore.user && !userStore.isLoading) {
        // If user data is not loaded and not currently loading, fetch it
        userStore
          .fetchUser()
          .then(() => {
            // Continue with navigation after fetching user data
            checkPermissionsAndProceed(to, next, userStore);
          })
          .catch(() => {
            // If fetching user data fails, continue anyway
            next();
          });
        return;
      }

      // If user data is still loading, we need to wait
      if (userStore.isLoading) {
        // Create a watcher to wait for loading to complete
        const unwatch = userStore.$subscribe(() => {
          if (!userStore.isLoading) {
            unwatch();
            // Continue with navigation after loading is complete
            checkPermissionsAndProceed(to, next, userStore);
          }
        });
        return;
      }

      // Check permissions if user data is available
      checkPermissionsAndProceed(to, next, userStore);
      return;
    }

    // Default: continue with navigation
    next();
  },
);

// Helper function to check permissions
function checkPermissionsAndProceed(
  to: RouteLocationNormalized,
  next: NavigationGuardNext,
  userStore: any,
) {
  // Super Admin bypasses all checks
  if (userStore.isSuperAdmin) {
    next();
    return;
  }

  // Check if route requires specific permission
  const requiredPermission = to.meta.permission as string | undefined;

  if (requiredPermission && !userStore.hasPermission(requiredPermission)) {
    // User doesn't have required permission
    console.warn(`Access denied: Missing permission "${requiredPermission}"`);
    next("/dashboard"); // Redirect to dashboard
    return;
  }

  // // Check if route requires specific role
  // const requiredRole = to.meta.role as string | undefined;

  // if (requiredRole && !userStore.hasRole(requiredRole)) {
  //   // User doesn't have required role
  //   console.warn(`Access denied: Missing role "${requiredRole}"`);
  //   next("/dashboard"); // Redirect to dashboard
  //   return;
  // }

  next();
}

export default router;
