import { z } from 'zod'

export const roleSchema = z.object({
  name: z.string()
    .min(3, 'Role name must be at least 3 characters')
    .regex(/^[a-zA-Z0-9_]+$/, 'Role name can only contain letters, numbers, and underscores'),
  permissions: z.array(z.string()),
})

export const updateRoleSchema = roleSchema.partial().refine(
  (data) => data.name || data.permissions,
  { message: 'At least one field must be provided' }
)

export type RoleForm = z.infer<typeof roleSchema>
export type UpdateRoleForm = z.infer<typeof updateRoleSchema>
