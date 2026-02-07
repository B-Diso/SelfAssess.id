<script setup lang="ts">
import { computed, h } from "vue";
import type { ColumnDef, SortingState } from "@tanstack/vue-table";
import type { Standard } from "../types/standard";
import DataTable from "@/components/ui/DataTable.vue";
import StandardListRowActions from "./StandardListRowActions.vue";
import { ArrowUpDown, ChevronDown, ChevronUp } from "lucide-vue-next";
import { Badge } from "@/components/ui/badge";

const props = withDefaults(
  defineProps<{
    standards: Standard[];
    sorting?: SortingState;
    page?: number;
    pageSize?: number;
    totalItems?: number;
    totalPages?: number;
  }>(),
  {
    standards: () => [],
    sorting: undefined,
  },
);

const emit = defineEmits<{
  (e: "edit", standard: Standard): void;
  (e: "delete", standard: Standard): void;
  (e: "view", standard: Standard): void;
  (e: "sorting-change", value: SortingState): void;
  (e: "page-size-change", value: number): void;
  (e: "page-change", value: number): void;
}>();

function renderSortableHeader(label: string) {
  return ({ column }: any) => {
    if (!column.getCanSort()) {
      return label;
    }

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
        onClick: (e: MouseEvent) => {
          e.preventDefault();
          column.toggleSorting(sortState === "asc");
        },
      },
      [h("span", label), icon],
    );
  };
}

function formatDate(dateString?: string): string {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

const columns = computed<ColumnDef<Standard>[]>(() => [
  {
    accessorKey: "name",
    size: 40,
    header: renderSortableHeader("Standard"),
    enableSorting: true,
    cell: ({ row }) => {
      const name = row.getValue("name") as string;
      const version = row.original.version;
      const type = row.original.type;

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
              class: "text-[10px] px-1.5 py-0 h-5 whitespace-nowrap uppercase tracking-wider font-bold transition-colors",
            },
            () => type ?? "-"
          ),
        ]),
      ]);
    },
  },
  {
    accessorKey: "periodType",
    size: 15,
    header: renderSortableHeader("Period"),
    cell: ({ row }) => {
      const type = row.original.periodType;
      if (!type)
        return h("span", { class: "text-xs text-muted-foreground" }, "-");
      return h("div", { class: "text-xs text-muted-foreground capitalize" }, type);
    },
  },
  {
    accessorKey: "isActive",
    size: 15,
    header: renderSortableHeader("Status"),
    enableSorting: true,
    cell: ({ row }) => {
      const isActive = row.original.isActive;
      return h(
        Badge,
        {
          variant: isActive ? "default" : "secondary",
          class: isActive
            ? "bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-50 px-1.5 h-5 text-[10px] whitespace-nowrap uppercase tracking-wider font-bold transition-colors"
            : "bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-50 px-1.5 h-5 text-[10px] whitespace-nowrap uppercase tracking-wider font-bold transition-colors",
        },
        () => (isActive ? "Active" : "Inactive"),
      );
    },
  },
  {
    accessorKey: "updatedAt",
    id: "updatedAt",
    size: 20,
    header: renderSortableHeader("Updated"),
    enableSorting: true,
    cell: ({ row }) =>
      h(
        "div",
        { class: "text-xs text-muted-foreground" },
        formatDate(row.getValue("updatedAt")),
      ),
  },
  {
    id: "actions",
    size: 10,
    enableHiding: false,
    cell: ({ row }) =>
      h(StandardListRowActions, {
        standard: row.original,
        onView: (standard: Standard) => emit("view", standard),
        onEdit: (standard: Standard) => emit("edit", standard),
        onDelete: (standard: Standard) => emit("delete", standard),
      }),
  },
]);

function handleSortingChange(value: SortingState) {
  emit("sorting-change", value);
}

function handlePageSizeChange(value: number) {
  emit("page-size-change", value);
}

function handlePageChange(value: number) {
  emit("page-change", value);
}
</script>

<template>
  <DataTable
    :columns="columns"
    :data="props.standards"
    :sorting="props.sorting"
    :page="props.page"
    :page-size="props.pageSize"
    :total-items="props.totalItems"
    :total-pages="props.totalPages"
    :compact="true"
    @sorting-change="handleSortingChange"
    @page-size-change="handlePageSizeChange"
    @page-change="handlePageChange"
  />
</template>
