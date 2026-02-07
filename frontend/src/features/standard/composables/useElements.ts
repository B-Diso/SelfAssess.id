import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { sectionApi } from '../api/standardApi'
import type { Ref } from 'vue'
import type {
  SectionFilters,
  CreateSectionRequest,
  UpdateSectionRequest,
} from '../types/standard'

// List elements query (elements are sections with type='element')
export function useElements(params: Ref<SectionFilters>) {
  return useQuery({
    queryKey: ['standards', 'sections', 'elements', params],
    queryFn: () => sectionApi.getSections(params.value.standardId),
  })
}

// Single element query
export function useElement(id: Ref<string>) {
  return useQuery({
    queryKey: ['standards', 'sections', id],
    queryFn: () => sectionApi.getSection(id.value),
    enabled: !!id.value,
  })
}

// Create element mutation
export function useCreateElement() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateSectionRequest) => sectionApi.createSectionDirect(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections', 'elements'] })
    },
  })
}

// Update element mutation
export function useUpdateElement() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, data }: { id: string; data: UpdateSectionRequest }) =>
      sectionApi.updateSection(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections', variables.id] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections', 'elements'] })
    },
  })
}

// Delete element mutation
export function useDeleteElement() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => sectionApi.deleteSection(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections'] })
      queryClient.invalidateQueries({ queryKey: ['standards', 'sections', 'elements'] })
    },
  })
}
