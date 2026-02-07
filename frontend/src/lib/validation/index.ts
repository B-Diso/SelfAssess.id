/**
 * Validation Utilities
 *
 * Shared utilities for form validation using vee-validate + zod
 */

import { useForm, useField } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import type { ZodSchema } from 'zod'
import type { MaybeRef } from 'vue'
import type { z } from 'zod'

/**
 * Wrapper untuk useForm dengan Zod schema
 * Auto-generates TypeScript types dari schema
 */
export function useZodForm<T extends ZodSchema>(
  schema: T,
  options?: {
    initialValues?: z.infer<T>
    onSubmit?: (values: z.infer<T>) => void | Promise<void>
  }
) {
  const typedSchema = toTypedSchema(schema)

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const form = useForm<any>({
    validationSchema: typedSchema,
    initialValues: options?.initialValues,
  })

  return {
    ...form,
    handleSubmit: form.handleSubmit,
  }
}

/**
 * Hook untuk field-level helpers
 */
export function useFieldHelpers(fieldName: MaybeRef<string>) {
  const { value, errorMessage, meta } = useField(fieldName)

  return {
    value,
    errorMessage,
    isTouched: meta.touched,
    isDirty: meta.dirty,
    isValid: meta.valid,
  }
}

// Export commonly used validation utilities
export { toTypedSchema, useForm, useField }
