/**
 * Common Validation Schemas
 *
 * Reusable schemas for common fields
 */

import { z } from 'zod'

// Email validation
export const emailSchema = z
  .string()
  .min(1, 'Email is required')
  .email('Please enter a valid email address')

// Password validation
export const passwordSchema = z
  .string()
  .min(8, 'Password must be at least 8 characters')
  .regex(/[A-Z]/, 'Password must contain at least one uppercase letter')
  .regex(/[a-z]/, 'Password must contain at least one lowercase letter')
  .regex(/[0-9]/, 'Password must contain at least one number')

// Simple password (for current password check)
export const simplePasswordSchema = z
  .string()
  .min(1, 'Password is required')

// UUID validation
export const uuidSchema = z
  .string()
  .uuid('Invalid ID format')

// Name validation
export const nameSchema = z
  .string()
  .min(2, 'Name must be at least 2 characters')
  .max(255, 'Name must not exceed 255 characters')
  .regex(/^[a-zA-Z\s'-]+$/, 'Name can only contain letters, spaces, hyphens and apostrophes')

// Description validation (optional)
export const descriptionSchema = z
  .string()
  .max(1000, 'Description must not exceed 1000 characters')
  .optional()
  .or(z.literal(''))

// Phone number validation
export const phoneSchema = z
  .string()
  .regex(/^\+?[\d\s-()]{8,20}$/, 'Please enter a valid phone number')
  .optional()
  .or(z.literal(''))

// Date validation (YYYY-MM-DD)
export const dateSchema = z
  .string()
  .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format')
  .optional()
  .or(z.literal(''))

// Required string
export const requiredString = (fieldName: string) =>
  z.string().min(1, `${fieldName} is required`)

// Export commonly used types
type Email = z.infer<typeof emailSchema>
type Password = z.infer<typeof passwordSchema>
type Uuid = z.infer<typeof uuidSchema>
type Name = z.infer<typeof nameSchema>

export type { Email, Password, Uuid, Name }
