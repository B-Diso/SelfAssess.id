<script setup lang="ts">
import { computed, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Loader2, ArrowLeft } from "lucide-vue-next";
import { watchDebounced } from "@vueuse/core";
import type { SortingState } from "@tanstack/vue-table";
import { useStandardOrganizations } from "../composables/useStandardOrganizations";
import { useStandard } from "../composables/useStandard";
import { Button } from "@/components/ui/button";
import DataTable from "@/components/ui/DataTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";
import { createColumns } from "../components/data-table/columns";

type SortField = "name" | "startedAt" | "progress" | "status";

const route = useRoute();
const router = useRouter();

const standardId = computed(() => route.params.id as string);

const searchInput = ref("");
const filters = reactive({
  page: 1,
  perPage: 15,
  search: "",
  sortBy: "name" as SortField,
  sortOrder: "asc" as "asc" | "desc",
});

const filtersRef = computed(() => ({ ...filters }));
const { data: organizationsData, isLoading, error } = useStandardOrganizations(standardId, filtersRef);
const { data: standardResponse, isLoading: isLoadingStandard } = useStandard(standardId);

const standard = computed(() => standardResponse.value?.data);
const organizations = computed(() => organizationsData.value?.data || []);
const totalItems = computed(() => organizationsData.value?.meta?.total ?? organizations.value.length);
const totalPages = computed(() => organizationsData.value?.meta?.lastPage ?? 1);

const tableSorting = ref<SortingState>([]);

const tableColumns = computed(() =>
  createColumns({
    onView: (assessmentId: string) => {
      router.push(`/assessments/${assessmentId}`);
    },
  }),
);

watch(
  () => [filters.sortBy, filters.sortOrder] as [SortField, "asc" | "desc"],
  ([sortBy, sortOrder]) => {
    tableSorting.value = [
      {
        id: sortBy,
        desc: sortOrder === "desc",
      },
    ];
  },
  { immediate: true },
);

watchDebounced(
  searchInput,
  (newValue) => {
    filters.search = newValue;
    filters.page = 1;
  },
  { debounce: 300 },
);

watch(
  () => filters.perPage,
  () => {
    filters.page = 1;
  },
);

function handlePageSizeChange(size: number) {
  filters.perPage = size;
}

function handlePageChange(page: number) {
  filters.page = page;
}

function handleSortingChange(sortingState: SortingState) {
  const [sort] = sortingState;

  if (!sort) {
    filters.sortBy = "name";
    filters.sortOrder = "asc";
    return;
  }

  if (
    sort.id === "name" ||
    sort.id === "startedAt" ||
    sort.id === "progress" ||
    sort.id === "status"
  ) {
    filters.sortBy = sort.id as SortField;
    filters.sortOrder = sort.desc ? "desc" : "asc";
  }
}

function resetFilters() {
  searchInput.value = "";
}

function goBack() {
  router.push("/standards/report");
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      :title="standard?.name || 'Standard Details'"
      description="Organizations and their assessment status for this standard."
    >
      <template #actions>
        <Button
          variant="outline"
          size="sm"
          class="h-8"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4 mr-1" />
          Back to Report
        </Button>
      </template>
    </PageHeader>

    <!-- Main Content Card -->
    <div class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border">
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search organization..."
        :show-reset="searchInput !== ''"
        @reset="resetFilters"
      />

      <!-- Table Section -->
      <div class="flex-1 min-h-0 p-3 flex flex-col">
        <div v-if="isLoading || isLoadingStandard" class="h-full flex items-center justify-center">
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="h-8 w-8 animate-spin text-primary" />
            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Loading organizations...
            </p>
          </div>
        </div>

        <div
          v-else-if="error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">Error loading organizations</p>
            <p class="text-xs text-muted-foreground mt-1">
              Please check your connection and try again.
            </p>
            <Button variant="outline" size="sm" class="mt-4" @click="() => {}">
              Retry
            </Button>
          </div>
        </div>

        <DataTable
          v-else
          :columns="tableColumns"
          :data="organizations"
          :sorting="tableSorting"
          :page="filters.page"
          :page-size="filters.perPage"
          :total-items="totalItems"
          :total-pages="totalPages"
          :compact="true"
          @sorting-change="handleSortingChange"
          @page-size-change="handlePageSizeChange"
          @page-change="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>
