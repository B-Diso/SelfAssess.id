import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { requirementApi } from '../api/standardApi'
import type { Ref } from 'vue'
import type {
  RequirementFilters,
  CreateRequirementRequest,
  UpdateRequirementRequest,
} from '../types/standard'

// List requirements query
export function useRequirements(params: Ref<RequirementFilters>) {
  return useQuery({
    queryKey: ['standards', 'requirements', params],
    queryFn: () => requirementApi.getRequirements(params.value),
  })
}

// Single requirement query
export function useRequirement(id: Ref<string>) {
  return useQuery({
    queryKey: ['standards', 'requirements', id],
    queryFn: () => requirementApi.getRequirement(id.value),
    enabled: !!id.value,
  })
}

// Create requirement mutation
export function useCreateRequirement() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ standardId, data }: { standardId: string; data: CreateRequirementRequest }) =>
      requirementApi.createRequirement(standardId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'requirements'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
    },
  })
}

// Update requirement mutation
export function useUpdateRequirement() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateRequirementRequest }) =>
      requirementApi.updateRequirement(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'requirements'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'requirements', variables.id] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
    },
  })
}

// Delete requirement mutation
export function useDeleteRequirement() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => requirementApi.deleteRequirement(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'requirements'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
    },
  })
}
