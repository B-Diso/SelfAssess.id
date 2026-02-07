import { z } from 'zod'
import { emailSchema, nameSchema, passwordSchema, uuidSchema } from './common'

export const createUserSchema = z.object({
  name: nameSchema,
  email: emailSchema,
  password: passwordSchema,
  organizationId: uuidSchema.optional().or(z.literal('')),
  roles: z.array(z.string()).min(1, 'At least one role is required'),
})

export const updateUserSchema = z.object({
  name: nameSchema.optional(),
  email: emailSchema.optional(),
  password: z.string().optional(),
  organizationId: uuidSchema.optional(),
  roles: z.array(z.string()).min(1, 'At least one role is required').optional(),
}).refine((data) => Object.keys(data).length > 0, {
  message: 'At least one field must be provided',
})

export type CreateUserForm = z.infer<typeof createUserSchema>
export type UpdateUserForm = z.infer<typeof updateUserSchema>
