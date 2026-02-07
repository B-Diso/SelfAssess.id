import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'
import { organizationApi } from '../api/organizationApi'
import type { Ref } from 'vue'
import type {
  OrganizationFilters,
  CreateOrganizationRequest,
  UpdateOrganizationRequest,
} from '../types/organization.types'

// List organizations query
export function useOrganizations(filters: Ref<OrganizationFilters>) {
  const queryKey = computed(() => [
    'organizations',
    filters.value.page,
    filters.value.perPage,
    filters.value.search,
    filters.value.sortBy,
    filters.value.sortOrder,
  ] as const)

  return useQuery({
    queryKey,
    queryFn: () => organizationApi.getOrganizations(filters.value),
  })
}

// Single organization query
export function useOrganization(id: Ref<string>) {
  return useQuery({
    queryKey: ['organizations', id],
    queryFn: () => organizationApi.getOrganization(id.value),
    enabled: !!id.value,
  })
}

// Organization members query
export function useOrganizationMembers(id: Ref<string>) {
  return useQuery({
    queryKey: ['organizations', id, 'members'],
    queryFn: () => organizationApi.getOrganizationMembers(id.value),
    enabled: !!id.value,
  })
}

// Create organization mutation
export function useCreateOrganization() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateOrganizationRequest) => organizationApi.createOrganization(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['organizations'] })
    },
  })
}

// Update organization mutation
export function useUpdateOrganization() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateOrganizationRequest }) =>
      organizationApi.updateOrganization(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['organizations'] })
      queryClient.invalidateQueries({ queryKey: ['organizations', variables.id] })
    },
  })
}

// Delete organization mutation
export function useDeleteOrganization() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => organizationApi.deleteOrganization(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['organizations'] })
    },
  })
}
