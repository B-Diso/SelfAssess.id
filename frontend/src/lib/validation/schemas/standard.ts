/**
 * Standard Domain Validation Schemas
 *
 * Validation schemas for Standard, Section, and Requirement forms
 */

import { z } from 'zod'
import { nameSchema } from './common'

// ============================================================================
// Standard Schemas
// ============================================================================

export const standardSchema = z.object({
  name: nameSchema.min(3, 'Standard name must be at least 3 characters'),
  version: z.string().min(1, 'Version is required'),
  type: z.enum(['internal', 'regulatory', 'standard', 'bestPractice', 'other']).default('internal'),
  description: z.string().optional().or(z.literal('')),
  periodType: z.enum(['annual', 'quarterly', 'semester', 'monthly']).optional().or(z.literal('')),
})

export const createStandardSchema = standardSchema

export const updateStandardSchema = standardSchema.partial({
  type: true,
  description: true,
  periodType: true,
}).refine(
  (data) => Object.keys(data).length > 0,
  {
    message: 'At least one field must be provided',
  }
)

// ============================================================================
// Section Schemas
// ============================================================================

export const sectionSchema = z.object({
  title: z.string().min(1, 'Title is required').min(3, 'Title must be at least 3 characters'),
  code: z.string().min(1, 'Code is required'),
  type: z.string().optional().or(z.literal('')),
  description: z.string().optional().or(z.literal('')),
})

export const createSectionSchema = sectionSchema.extend({
  parentId: z.string().uuid().optional().or(z.literal('')),
})

export const updateSectionSchema = sectionSchema.partial().refine(
  (data) => Object.keys(data).length > 0,
  {
    message: 'At least one field must be provided',
  }
)

// ============================================================================
// Requirement Schemas
// ============================================================================

export const requirementSchema = z.object({
  title: z.string().min(1, 'Title is required').min(3, 'Title must be at least 3 characters'),
  displayCode: z.string().min(1, 'Display Code is required'),
  description: z.string().optional().or(z.literal('')),
  evidenceHint: z.string().optional().or(z.literal('')),
})

export const createRequirementSchema = requirementSchema

export const updateRequirementSchema = requirementSchema.partial().refine(
  (data) => Object.keys(data).length > 0,
  {
    message: 'At least one field must be provided',
  }
)

// ============================================================================
// Type Exports
// ============================================================================

export type StandardForm = z.infer<typeof standardSchema>
export type CreateStandardForm = z.infer<typeof createStandardSchema>
export type UpdateStandardForm = z.infer<typeof updateStandardSchema>

export type SectionForm = z.infer<typeof sectionSchema>
export type CreateSectionForm = z.infer<typeof createSectionSchema>
export type UpdateSectionForm = z.infer<typeof updateSectionSchema>

export type RequirementForm = z.infer<typeof requirementSchema>
export type CreateRequirementForm = z.infer<typeof createRequirementSchema>
export type UpdateRequirementForm = z.infer<typeof updateRequirementSchema>
