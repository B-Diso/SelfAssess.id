import type { ColumnDef } from "@tanstack/vue-table";
import type { Organization } from "@/features/organization/types/organization.types";
import { h } from "vue";
import { Badge } from "@/components/ui/badge";
import { ArrowUpDown, ChevronDown, ChevronUp } from "lucide-vue-next";
import DataTableDropdown from "./data-table-dropdown.vue";

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
          "flex items-center cursor-pointer select-none hover:text-foreground transition-colors py-1 px-2 -ml-2 rounded-md hover:bg-muted/50 font-medium",
        onClick: (e: MouseEvent) => {
          e.preventDefault();
          column.toggleSorting(sortState === "asc");
        },
      },
      [h("span", label), icon],
    );
  };
}

export function createColumns(handlers: {
  onEdit: (org: Organization) => void;
  onDelete: (org: Organization) => void;
  onViewMembers: (org: Organization) => void;
  onToggleStatus: (org: Organization) => void;
}): ColumnDef<Organization>[] {
  return [
    {
      accessorKey: "name",
      header: renderSortableHeader("Name"),
      enableSorting: true,
      size: 28,
      cell: ({ row }) =>
        h("div", { class: "font-medium text-sm" }, row.getValue("name")),
    },
    {
      accessorKey: "description",
      header: "Description",
      size: 23,
      cell: ({ row }) => {
        const description = row.getValue("description") as string | null;
        return h(
          "div",
          { class: "text-muted-foreground text-xs max-w-md truncate" },
          description || "-",
        );
      },
    },
    {
      accessorKey: "isActive",
      header: "Status",
      size: 11,
      cell: ({ row }) => {
        const isActive = row.getValue("isActive");
        return h(
          Badge,
          {
            variant: "outline",
            class: `text-[10px] uppercase tracking-wider px-1.5 py-0 h-5 whitespace-nowrap font-bold transition-colors ${
              isActive
                ? "bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100"
                : "bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200"
            }`,
          },
          () => (isActive ? "Active" : "Inactive"),
        );
      },
    },
    {
      accessorKey: "userCount",
      header: "Total Users",
      size: 11,
      cell: ({ row }) => {
        const count = row.getValue("userCount") as number | undefined;
        return h(
          "div",
          { class: "text-sm text-center" },
          count !== undefined ? count.toString() : "-",
        );
      },
    },
    {
      accessorKey: "createdAt",
      header: renderSortableHeader("Created At"),
      enableSorting: true,
      size: 17,
      cell: ({ row }) => {
        const date = new Date(row.getValue("createdAt"));
        return h(
          "div",
          { class: "text-xs text-muted-foreground" },
          date.toLocaleDateString(),
        );
      },
    },
    {
      id: "actions",
      enableHiding: false,
      size: 10,
      cell: ({ row }) =>
        h(DataTableDropdown, {
          organization: row.original,
          onEdit: handlers.onEdit,
          onDelete: handlers.onDelete,
          onViewMembers: handlers.onViewMembers,
          onToggleStatus: handlers.onToggleStatus,
        }),
    },
  ];
}
