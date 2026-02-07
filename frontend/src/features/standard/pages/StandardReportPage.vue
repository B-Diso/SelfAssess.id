<script setup lang="ts">
import { computed, h, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { Loader2, Eye, ArrowUpDown, ChevronUp, ChevronDown } from "lucide-vue-next";
import { watchDebounced } from "@vueuse/core";
import type { SortingState } from "@tanstack/vue-table";
import { useStandardReport } from "../composables/useStandardReport";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import DataTable from "@/components/ui/DataTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";

const router = useRouter();
const searchInput = ref("");
const filters = reactive({
  page: 1,
  perPage: 15,
  search: "",
  sortBy: "name" as const,
  sortOrder: "asc" as "asc" | "desc",
});

const filtersRef = computed(() => ({ ...filters }));
const { data: reportData, isLoading, error } = useStandardReport(filtersRef);

const standards = computed(() => reportData.value?.data || []);
const totalItems = computed(
  () => reportData.value?.meta?.total ?? standards.value.length,
);
const totalPages = computed(() => reportData.value?.meta?.lastPage ?? 1);

const tableSorting = ref<SortingState>([]);

const columns = computed(() => [
  {
    accessorKey: "name",
    size: 35,
    header: ({ column }: any) => {
      const sortState = column.getIsSorted();
      const icon =
        sortState === "asc"
          ? h(ChevronUp, { class: "ml-2 h-4 w-4" })
          : sortState === "desc"
            ? h(ChevronDown, { class: "ml-2 h-4 w-4" })
            : h(ArrowUpDown, { class: "ml-2 h-4 w-4 text-muted-foreground" });

      return h(
        "div",
        {
          class:
            "flex items-center cursor-pointer select-none hover:text-foreground transition-colors py-1 px-2 -ml-2 rounded-md hover:bg-muted/50 text-xs font-medium uppercase tracking-wider",
          onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
        },
        [h("span", "Standard"), icon],
      );
    },
    cell: ({ row }: any) => {
      const name = row.getValue("name") as string;
      const type = row.original.type as string;
      const version = row.original.version as string;
      return h("div", { class: "flex flex-col gap-1" }, [
        h("div", { class: "font-medium text-sm" }, name),
        h("div", { class: "flex items-center gap-2" }, [
          h(
            "span",
            { class: "font-mono text-xs text-muted-foreground" },
            version
          ),
          h(
            Badge,
            {
              variant: "outline",
              class:
                "text-[10px] uppercase tracking-wider px-1.5 py-0 h-5 whitespace-nowrap font-bold transition-colors",
            },
            () => type,
          ),
        ]),
      ]);
    },
  },
  {
    accessorKey: "stats",
    size: 35,
    header: () => {
      return h(
        "div",
        {
          class:
            "flex items-center cursor-pointer select-none hover:text-foreground transition-colors py-1 px-2 -ml-2 rounded-md hover:bg-muted/50 text-xs font-medium uppercase tracking-wider",
        },
        [h("span", "Organizations")],
      );
    },
    cell: ({ row }: any) => {
      const stats = row.getValue("stats") as {
        totalOrganizations: number;
        startedOrganizations: number;
        notStartedOrganizations: number;
      };
      return h("div", { class: "flex flex-col gap-1" }, [
        h("div", { class: "flex items-center gap-2 text-xs" }, [
          h("span", { class: "text-muted-foreground" }, "Started:"),
          h("span", { class: "font-medium text-emerald-600" }, stats.startedOrganizations),
        ]),
        h("div", { class: "flex items-center gap-2 text-xs" }, [
          h("span", { class: "text-muted-foreground" }, "Not Started:"),
          h("span", { class: "font-medium text-amber-600" }, stats.notStartedOrganizations),
        ]),
      ]);
    },
  },
  {
    accessorKey: "createdAt",
    size: 20,
    header: ({ column }: any) => {
      const sortState = column.getIsSorted();
      const icon =
        sortState === "asc"
          ? h(ChevronUp, { class: "ml-2 h-4 w-4" })
          : sortState === "desc"
            ? h(ChevronDown, { class: "ml-2 h-4 w-4" })
            : h(ArrowUpDown, { class: "ml-2 h-4 w-4 text-muted-foreground" });

      return h(
        "div",
        {
          class:
            "flex items-center cursor-pointer select-none hover:text-foreground transition-colors py-1 px-2 -ml-2 rounded-md hover:bg-muted/50 text-xs font-medium uppercase tracking-wider",
          onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
        },
        [h("span", "Created"), icon],
      );
    },
    cell: ({ row }: any) => {
      const createdAt = row.getValue("createdAt") as string;
      const date = new Date(createdAt);
      return h("div", { class: "text-xs text-muted-foreground" }, date.toLocaleDateString());
    },
  },
  {
    id: "actions",
    size: 10,
    enableHiding: false,
    cell: ({ row }: any) => {
      const standard = row.original;
      return h("div", { class: "flex justify-end" }, [
        h(
          Button,
          {
            variant: "ghost",
            size: "sm",
            class: "h-8 w-8 p-0",
            onClick: () => onViewOrganizations(standard.id),
          },
          () => h(Eye, { class: "h-4 w-4" }),
        ),
      ]);
    },
  },
]);

watch(
  () => [filters.sortBy, filters.sortOrder] as [string, "asc" | "desc"],
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
  filters.sortBy = sort.id as any;
  filters.sortOrder = sort.desc ? "desc" : "asc";
}

function resetFilters() {
  searchInput.value = "";
}

// Action handlers
function onViewOrganizations(standardId: string) {
  // Navigate to organizations page for this standard
  router.push(`/standards/${standardId}/organizations`);
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Standard Report"
      description="View assessment adoption across all organizations by standard."
    />

    <!-- Main Content Card -->
    <div
      class="flex-1 flex flex-col overflow-hidden bg-white rounded-lg shadow-sm border"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search standard..."
        :show-reset="searchInput !== ''"
        @reset="resetFilters"
      />

      <!-- Table Section -->
      <div class="flex-1 min-h-0 p-3 flex flex-col">
        <div v-if="isLoading" class="h-full flex items-center justify-center">
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="h-8 w-8 animate-spin text-primary" />
            <p
              class="text-xs font-medium text-muted-foreground uppercase tracking-wider"
            >
              Loading standards...
            </p>
          </div>
        </div>

        <div
          v-else-if="error"
          class="h-full flex items-center justify-center p-8 text-center"
        >
          <div class="max-w-xs">
            <p class="text-sm font-medium text-red-600">
              Error loading standards
            </p>
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
          :columns="columns"
          :data="standards"
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
