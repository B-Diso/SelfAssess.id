import { useQuery } from '@tanstack/vue-query'
import { computed } from 'vue'
import { standardApi } from '../api/standardApi'
import type { Ref } from 'vue'
import type { StandardReportFilters } from '../types/standard'

// List standard report query
export function useStandardReport(filters: Ref<StandardReportFilters>) {
  const queryKey = computed(() => [
    'standard-report',
    filters.value.page,
    filters.value.perPage,
    filters.value.search,
    filters.value.sortBy,
    filters.value.sortOrder,
  ] as const)

  return useQuery({
    queryKey,
    queryFn: () => standardApi.getStandardReport(filters.value),
  })
}
