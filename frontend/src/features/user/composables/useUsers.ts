import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'
import { userApi } from '../api/userApi'
import type { Ref } from 'vue'
import type {
  UserFilters,
  CreateUserRequest,
  UpdateUserRequest,
  AssignRoleRequest,
} from '../types/user.types'

// List users query
export function useUsers(filters: Ref<UserFilters>) {
  const queryKey = computed(() => [
    'users',
    filters.value.page,
    filters.value.perPage,
    filters.value.search,
    filters.value.role,
    filters.value.sortBy,
    filters.value.sortOrder,
  ] as const)

  return useQuery({
    queryKey: queryKey,
    queryFn: () => userApi.getUsers(filters.value),
  })
}

// Single user query
export function useUser(id: Ref<string>) {
  const queryKey = computed(() => ['users', id.value] as const)

  return useQuery({
    queryKey: queryKey,
    queryFn: () => userApi.getUser(id.value),
    enabled: !!id.value,
  })
}

// Create user mutation
export function useCreateUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateUserRequest) => userApi.createUser(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}

// Update user mutation
export function useUpdateUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateUserRequest }) =>
      userApi.updateUser(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
      queryClient.invalidateQueries({ queryKey: ['users', variables.id] })
    },
  })
}

// Delete user mutation
export function useDeleteUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => userApi.deleteUser(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}

// Assign role mutation
export function useAssignRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: AssignRoleRequest }) =>
      userApi.assignRole(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
      queryClient.invalidateQueries({ queryKey: ['users', variables.id] })
    },
  })
}

// Update role mutation
export function useUpdateRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: AssignRoleRequest }) =>
      userApi.updateRole(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
      queryClient.invalidateQueries({ queryKey: ['users', variables.id] })
    },
  })
}
