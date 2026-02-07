import type { ClassValue } from "clsx"
import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"
import type { Updater } from "@tanstack/vue-table"
import type { Ref } from "vue"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function valueUpdater<T extends Updater<any>>(
  updaterOrValue: T,
  ref: Ref,
): void {
  ref.value =
    typeof updaterOrValue === "function"
      ? updaterOrValue(ref.value)
      : updaterOrValue
}

/**
 * Transform object keys from camelCase to snake_case
 * Used for API request parameters
 */
export function toSnakeCase(obj: Record<string, any>): Record<string, any> {
  if (!obj) return obj

  const transformed: Record<string, any> = {}

  for (const [key, value] of Object.entries(obj)) {
    // Convert camelCase to snake_case
    const snakeKey = key.replace(/([A-Z])/g, "_$1").toLowerCase()
    transformed[snakeKey] = value
  }

  return transformed
}
