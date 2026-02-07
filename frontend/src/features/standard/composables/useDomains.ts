import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { sectionApi } from '../api/standardApi'
import type { Ref } from 'vue'
import type {
  SectionFilters,
  CreateSectionRequest,
  UpdateSectionRequest,
} from '../types/standard'

// List sections query
export function useDomains(params: Ref<SectionFilters>) {
  return useQuery({
    queryKey: ['standards', 'sections', params],
    queryFn: () => sectionApi.getSections(params.value.standardId),
  })
}

// Single section query
export function useDomain(id: Ref<string>) {
  return useQuery({
    queryKey: ['standards', 'sections', id],
    queryFn: () => sectionApi.getSection(id.value),
    enabled: !!id.value,
  })
}

// Create section mutation
export function useCreateDomain() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateSectionRequest) => sectionApi.createSectionDirect(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
    },
  })
}

// Update section mutation
export function useUpdateDomain() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateSectionRequest }) =>
      sectionApi.updateSection(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections', variables.id] })
    },
  })
}

// Delete section mutation
export function useDeleteDomain() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => sectionApi.deleteSection(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
    },
  })
}
