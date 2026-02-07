/**
 * Organization Validation Schemas
 *
 * Validation schemas for organization forms using zod
 */

import { z } from 'zod'
import { nameSchema } from './common'

export const organizationSchema = z.object({
  name: nameSchema.min(3, 'Organization name must be at least 3 characters'),
  description: z.string().max(1000, 'Description too long').optional(),
  isActive: z.boolean().default(true),
})

export type OrganizationForm = z.infer<typeof organizationSchema>
