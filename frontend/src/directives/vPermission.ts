import { type Directive, type DirectiveBinding } from 'vue'
import { usePermissions } from '@/composables/userPermissions'

/**
 * v-permission directive
 * Shows element only if user has the specified permission
 * 
 * Usage:
 * <button v-permission="'create_users'">Create User</button>
 * <div v-permission="'delete_users'">Delete Button</div>
 */
export const vPermission: Directive = {
  mounted(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const { hasPermission } = usePermissions()
    const permission = binding.value

    if (!hasPermission(permission)) {
      el.parentNode?.removeChild(el)
    }
  },
  updated(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const { hasPermission } = usePermissions()
    const permission = binding.value

    if (!hasPermission(permission)) {
      el.parentNode?.removeChild(el)
    }
  },
}

/**
 * v-permission-not directive
 * Shows element if user DOES NOT have the specified permission
 * 
 * Usage:
 * <span v-permission-not="'view_sensitive_data'">You cannot see this</span>
 */
export const vPermissionNot: Directive = {
  mounted(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const { hasPermission } = usePermissions()
    const permission = binding.value

    if (hasPermission(permission)) {
      el.parentNode?.removeChild(el)
    }
  },
  updated(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const { hasPermission } = usePermissions()
    const permission = binding.value

    if (hasPermission(permission)) {
      el.parentNode?.removeChild(el)
    }
  },
}

/**
 * v-permission-else directive
 * Shows element if user has permission, otherwise shows else slot
 * 
 * Usage:
 * <component v-permission-else="'edit_users'" :is="hasPermission ? EditButton : ViewButton" />
 */
export const vPermissionElse: Directive = {
  mounted(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const { hasPermission } = usePermissions()
    const permission = binding.value

    // Store the permission state on the element
    ;(el as any).__hasPermission__ = hasPermission(permission)
  },
  updated(el: HTMLElement, binding: DirectiveBinding<string>): void {
    const { hasPermission } = usePermissions()
    const permission = binding.value

    // Update the permission state on the element
    ;(el as any).__hasPermission__ = hasPermission(permission)
  },
}

// Export directive names for registration
export const PermissionDirectiveNames = {
  Permission: 'permission',
  PermissionNot: 'permission-not',
  PermissionElse: 'permission-else',
} as const

// Type exports for directive registration
export type PermissionDirectiveType = typeof vPermission | typeof vPermissionNot | typeof vPermissionElse
