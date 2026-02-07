<script setup lang="ts" generic="TData, TValue">
import type {
  ColumnDef,
  SortingState,
  ColumnFiltersState,
  VisibilityState,
} from "@tanstack/vue-table";
import {
  FlexRender,
  getCoreRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  useVueTable,
} from "@tanstack/vue-table";

import { valueUpdater, cn } from "@/lib/utils";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";
import { ScrollArea } from "@/components/ui/scroll-area";
import { computed, ref, watch } from "vue";

interface DataTableProps {
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
  sorting?: SortingState;
  page?: number;
  pageSize?: number;
  pageSizeOptions?: number[];
  totalItems?: number;
  totalPages?: number;
  statusFilterOptions?: { value: string; label: string }[];
  currentStatus?: string;
  showStatusFilter?: boolean;
  compact?: boolean;
  maxHeight?: string;
}

const props = withDefaults(defineProps<DataTableProps>(), {
  showStatusFilter: false,
  compact: true,
});

const emit = defineEmits<{
  (e: "sorting-change", value: SortingState): void;
  (e: "page-size-change", value: number): void;
  (e: "page-change", value: number): void;
  (e: "status-change", value: string): void;
  (e: "row-click", row: TData): void;
}>();

// Local state
const sorting = ref<SortingState>(props.sorting ? [...props.sorting] : []);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});

// Computed
const pageSizeOptions = computed(
  () => props.pageSizeOptions ?? [15, 30, 60, 100],
);
const pageSizeNumber = computed(
  () => props.pageSize ?? pageSizeOptions.value[0] ?? 15,
);
const totalRows = computed(() => props.totalItems ?? props.data.length);
const totalPages = computed(
  () =>
    props.totalPages ??
    Math.max(1, Math.ceil(totalRows.value / Math.max(pageSizeNumber.value, 1))),
);
const currentPage = computed(() => {
  const page = props.page ?? 1;
  if (page < 1) return 1;
  if (page > totalPages.value) return totalPages.value;
  return page;
});
const showPagination = computed(() => totalPages.value > 1);
const paginationItems = computed(() => {
  const pages: Array<number | "ellipsis"> = [];
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 7) {
    for (let page = 1; page <= total; page += 1) {
      pages.push(page);
    }
    return pages;
  }

  pages.push(1);

  const showLeadingEllipsis = current > 4;
  const showTrailingEllipsis = current < total - 3;

  const start = Math.max(2, current - 1);
  const end = Math.min(total - 1, current + 1);

  if (showLeadingEllipsis) {
    pages.push("ellipsis");
  } else {
    for (let page = 2; page < start; page += 1) {
      pages.push(page);
    }
  }

  for (let page = start; page <= end; page += 1) {
    pages.push(page);
  }

  if (showTrailingEllipsis) {
    pages.push("ellipsis");
  } else {
    for (let page = end + 1; page < total; page += 1) {
      pages.push(page);
    }
  }

  pages.push(total);
  return pages;
});

// Watch for external sorting changes
watch(
  () => props.sorting,
  (newSorting) => {
    if (!newSorting) {
      sorting.value = [];
      return;
    }

    const serializedCurrent = JSON.stringify(sorting.value);
    const serializedIncoming = JSON.stringify(newSorting);

    if (serializedCurrent !== serializedIncoming) {
      sorting.value = [...newSorting];
    }
  },
  { deep: true },
);

// Table setup - use data directly (server-side pagination)
const table = useVueTable({
  get data() {
    return props.data;
  },
  get columns() {
    return props.columns;
  },
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
  },
  onSortingChange: (updaterOrValue) => {
    valueUpdater(updaterOrValue, sorting);
    emit("sorting-change", sorting.value);
  },
  onColumnFiltersChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnVisibility),
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
});

// Page size model
const pageSizeModel = computed({
  get: () => pageSizeNumber.value.toString(),
  set: (value: string) => {
    const size = Number(value);
    if (!Number.isFinite(size) || size <= 0) {
      return;
    }

    emit("page-size-change", size);
  },
});

// Handlers
function handlePageUpdate(page: number) {
  emit("page-change", page);
}

function handleStatusChange(value: unknown) {
  if (typeof value === "string") {
    emit("status-change", value);
  }
}
</script>

<template>
  <div
    :class="
      cn(
        'flex flex-col w-full h-full overflow-hidden',
        compact ? 'gap-2' : 'gap-4',
      )
    "
  >
    <!-- Status Filter / Header Slot -->
    <div
      v-if="(showStatusFilter && statusFilterOptions) || $slots.header"
      class="flex items-center justify-between gap-4 px-1 shrink-0"
    >
      <div
        v-if="showStatusFilter && statusFilterOptions"
        class="flex items-center gap-2"
      >
        <span
          class="text-xs font-medium text-muted-foreground uppercase tracking-wider"
          >Status:</span
        >
        <Select
          :model-value="currentStatus ?? ''"
          @update:model-value="
            (val: unknown) => typeof val === 'string' && handleStatusChange(val)
          "
        >
          <SelectTrigger
            :class="cn('w-40', compact ? 'h-8 text-xs' : 'h-10 text-sm')"
          >
            <SelectValue :placeholder="'All Status'" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="option in statusFilterOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <slot name="header" />
    </div>

    <!-- Table -->
    <div class="flex-1 min-h-0 overflow-hidden">
      <slot name="table-wrapper" :table="table">
        <div class="h-full rounded-md border overflow-hidden bg-card flex flex-col">
          <ScrollArea class="flex-1 w-full overflow-hidden">
            <Table class="w-full relative border-collapse table-fixed">
              <TableHeader
                class="sticky top-0 z-10 bg-muted/95 backdrop-blur-sm shadow-sm"
              >
                <TableRow
                  v-for="headerGroup in table.getHeaderGroups()"
                  :key="headerGroup.id"
                  class="hover:bg-transparent border-none"
                >
                  <TableHead
                    v-for="header in headerGroup.headers"
                    :key="header.id"
                    :compact="compact"
                    :style="{ width: header.column.columnDef.size ? `${header.column.columnDef.size}px` : 'auto' }"
                  >
                    <FlexRender
                      v-if="!header.isPlaceholder"
                      :render="header.column.columnDef.header"
                      :props="header.getContext()"
                    />
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                  <TableRow
                    v-for="row in table.getRowModel().rows"
                    :key="row.id"
                    :class="cn('', compact ? 'even:bg-muted/20' : '')"
                  >
                    <TableCell
                      v-for="cell in row.getVisibleCells()"
                      :key="cell.id"
                      :compact="compact"
                      :style="{ width: cell.column.columnDef.size ? `${cell.column.columnDef.size}px` : 'auto' }"
                    >
                      <slot
                        :name="`cell-${cell.column.id}`"
                        :cell="cell"
                        :row="row"
                        :value="cell.getValue()"
                      >
                        <FlexRender
                          :render="cell.column.columnDef.cell"
                          :props="cell.getContext()"
                        />
                      </slot>
                    </TableCell>
                  </TableRow>
                </template>

                <TableRow v-else>
                  <TableCell
                    :colspan="table.getAllColumns().length"
                    class="h-24 text-center text-muted-foreground italic"
                  >
                    No data available
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </ScrollArea>
        </div>
      </slot>
    </div>

    <!-- Pagination -->
    <div
      :class="
        cn(
          'flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between px-1 shrink-0',
          compact ? 'mt-1 mb-1' : 'mt-2',
        )
      "
    >
      <div
        class="text-[11px] font-medium text-muted-foreground uppercase tracking-tight"
      >
        Showing {{ Math.max(0, (currentPage - 1) * pageSizeNumber + 1) }} to
        {{ Math.min(currentPage * pageSizeNumber, totalRows) }} of
        {{ totalRows }} results
      </div>

      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
        <!-- Page Size Selector -->
        <div class="flex items-center gap-2">
          <span
            class="text-[11px] font-medium text-muted-foreground uppercase tracking-tight"
            >Per Page</span
          >
          <Select v-model="pageSizeModel">
            <SelectTrigger
              :class="cn('w-20', compact ? 'h-7 text-[11px]' : 'h-9 text-xs')"
            >
              <SelectValue :placeholder="pageSizeNumber.toString()" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="option in pageSizeOptions"
                :key="option"
                :value="option.toString()"
                class="text-xs"
              >
                {{ option }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Pagination Controls -->
        <Pagination
          v-if="showPagination"
          :total="totalRows"
          :items-per-page="pageSizeNumber"
          :page="currentPage"
          @update:page="handlePageUpdate"
        >
          <PaginationContent>
            <PaginationPrevious :class="compact ? 'h-7 px-2' : ''" />
            <template
              v-for="(item, index) in paginationItems"
              :key="
                typeof item === 'number' ? `page-${item}` : `ellipsis-${index}`
              "
            >
              <PaginationItem
                v-if="typeof item === 'number'"
                :value="item"
                :is-active="item === currentPage"
                :class="compact ? 'h-7 w-7 text-xs' : ''"
              >
                {{ item }}
              </PaginationItem>
              <PaginationEllipsis v-else />
            </template>
            <PaginationNext :class="compact ? 'h-7 px-2' : ''" />
          </PaginationContent>
        </Pagination>
      </div>
    </div>
  </div>
</template>
