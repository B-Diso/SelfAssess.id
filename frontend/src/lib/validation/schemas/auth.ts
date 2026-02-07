import { z } from 'zod'
import { emailSchema, nameSchema } from './common'

export const loginSchema = z.object({
  email: emailSchema,
  password: z.string().min(1, 'Password is required'),
  rememberMe: z.boolean().default(true),
})

export const profileSchema = z.object({
  name: nameSchema,
  email: emailSchema,
})

export const passwordSchema = z.object({
  currentPassword: z.string().min(1, 'Current password is required'),
  newPassword: z.string().min(8, 'Password must be at least 8 characters'),
  confirmPassword: z.string().min(1, 'Please confirm your password'),
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: "Passwords don't match",
  path: ['confirmPassword'],
})

export type LoginForm = z.infer<typeof loginSchema>
export type ProfileForm = z.infer<typeof profileSchema>
export type PasswordForm = z.infer<typeof passwordSchema>
