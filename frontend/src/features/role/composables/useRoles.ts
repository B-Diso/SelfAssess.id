import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { roleApi } from '../api/roleApi'
import type { Ref } from 'vue'
import type {
  CreateRoleRequest,
  UpdateRoleRequest,
} from '../types/role.types'

// List roles query
export function useRoles() {
  return useQuery({
    queryKey: ['roles'],
    queryFn: () => roleApi.getRoles(),
  })
}

// Single role query
export function useRole(id: Ref<number>) {
  return useQuery({
    queryKey: ['roles', id],
    queryFn: () => roleApi.getRole(id.value),
    enabled: !!id.value,
  })
}

// Permissions query
export function usePermissions() {
  return useQuery({
    queryKey: ['permissions'],
    queryFn: () => roleApi.getPermissions(),
  })
}

// Create role mutation
export function useCreateRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateRoleRequest) => roleApi.createRole(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['roles'] })
    },
  })
}

// Update role mutation
export function useUpdateRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateRoleRequest }) =>
      roleApi.updateRole(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['roles'] })
      queryClient.invalidateQueries({ queryKey: ['roles', variables.id] })
    },
  })
}

// Delete role mutation
export function useDeleteRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) => roleApi.deleteRole(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['roles'] })
    },
  })
}
