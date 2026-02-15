import { type App } from 'vue'
import { vPermission, vPermissionNot, vPermissionElse, PermissionDirectiveNames } from './vPermission'

/**
 * Register all permission directives with the Vue app
 */
export function registerPermissionDirectives(app: App) {
  app.directive(PermissionDirectiveNames.Permission, vPermission)
  app.directive(PermissionDirectiveNames.PermissionNot, vPermissionNot)
  app.directive(PermissionDirectiveNames.PermissionElse, vPermissionElse)
}

// Export individual directives for more granular control
export {
  vPermission,
  vPermissionNot,
  vPermissionElse,
  PermissionDirectiveNames,
}

// Export types
export type { PermissionDirectiveType } from './vPermission'
