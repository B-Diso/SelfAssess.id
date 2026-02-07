import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'
import { standardApi } from '../api/standardApi'
import type { Ref } from 'vue'
import type {
  StandardFilters,
  CreateStandardRequest,
  UpdateStandardRequest,
} from '../types/standard'

// List standards query
export function useStandards(filters: Ref<StandardFilters>) {
  const queryKey = computed(() => [
    'standards',
    filters.value.page,
    filters.value.perPage,
    filters.value.search,
    filters.value.type,
    filters.value.isActive,
    filters.value.sortBy,
    filters.value.sortOrder,
  ] as const)

  return useQuery({
    queryKey,
    queryFn: () => standardApi.getStandards(filters.value),
  })
}

// Single standard query
export function useStandard(id: Ref<string>) {
  return useQuery({
    queryKey: ['standards', id],
    queryFn: () => standardApi.getStandard(id.value),
    enabled: !!id.value,
  })
}

// Create standard mutation
export function useCreateStandard() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateStandardRequest) => standardApi.createStandard(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards'] })
    },
  })
}

// Update standard mutation
export function useUpdateStandard() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateStandardRequest }) =>
      standardApi.updateStandard(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['standards'] })
      queryClient.invalidateQueries({ queryKey: ['standards', variables.id] })
    },
  })
}

// Delete standard mutation
export function useDeleteStandard() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => standardApi.deleteStandard(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards'] })
    },
  })
}
