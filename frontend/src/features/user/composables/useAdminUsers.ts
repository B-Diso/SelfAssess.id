import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'
import type { Ref } from 'vue'
import { adminUserApi } from '../api/adminUserApi'
import type {
  AdminUserFilters,
  AssignAdminRoleRequest,
  CreateAdminUserRequest,
  UpdateAdminUserRequest,
} from '../types/adminUser.types'

export function useAdminUsers(filters: Ref<AdminUserFilters>) {
  const queryKey = computed(() => [
    'admin-users',
    filters.value.page,
    filters.value.perPage,
    filters.value.search,
    filters.value.organizationId,
    filters.value.role,
    filters.value.sortBy,
    filters.value.sortOrder,
  ] as const)

  return useQuery({
    queryKey,
    queryFn: () => adminUserApi.getUsers(filters.value),
    refetchOnMount: true,
  })
}

export function useCreateAdminUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateAdminUserRequest) => adminUserApi.createUser(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-users'] })
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}

export function useUpdateAdminUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateAdminUserRequest }) =>
      adminUserApi.updateUser(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-users'] })
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}

export function useDeleteAdminUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => adminUserApi.deleteUser(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-users'] })
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}

export function useAssignAdminRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: AssignAdminRoleRequest }) =>
      adminUserApi.updateUser(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-users'] })
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}
