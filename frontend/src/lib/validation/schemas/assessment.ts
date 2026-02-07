/**
 * Assessment Validation Schemas
 *
 * Validation schemas for assessment forms using zod
 */

import { z } from 'zod'
import { requiredString } from './common'

export const createAssessmentSchema = z.object({
  name: requiredString('Assessment name').min(3, 'Name must be at least 3 characters'),
  standardId: z.string().min(1, 'Standard is required'),
  organizationId: z.string().optional(),
  periodValue: z.string().max(255, 'Period value must be less than 255 characters').optional(),
  startDate: z.string().optional(),
  endDate: z.string().optional(),
})

export const actionPlanSchema = z.object({
  title: requiredString('Title').min(3, 'Title must be at least 3 characters'),
  actionPlan: z.string().min(10, 'Description must be at least 10 characters'),
  pic: requiredString('PIC'),
  dueDate: z.string().min(1, 'Due date is required'),
})

export type CreateAssessmentForm = z.infer<typeof createAssessmentSchema>
export type ActionPlanForm = z.infer<typeof actionPlanSchema>
