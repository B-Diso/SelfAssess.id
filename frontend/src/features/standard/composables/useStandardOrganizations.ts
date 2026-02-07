import { useQuery } from '@tanstack/vue-query'
import { computed, type Ref } from 'vue'
import { standardApi } from '../api/standardApi'

interface OrganizationFilters {
  page: number
  perPage: number
  search: string
  sortBy: string
  sortOrder: string
}

// Get organizations using a specific standard with filters
export function useStandardOrganizations(
  standardId: Ref<string>,
  filters: Ref<OrganizationFilters>
) {
  const queryKey = computed(() => [
    'standards',
    standardId.value,
    'organizations',
    filters.value,
  ] as const)

  return useQuery({
    queryKey,
    queryFn: () => standardApi.getStandardOrganizations(standardId.value, filters.value),
    enabled: !!standardId.value,
  })
}
