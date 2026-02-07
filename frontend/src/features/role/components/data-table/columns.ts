import type { ColumnDef } from "@tanstack/vue-table";
import type { Role } from "@/features/role/types/role.types";
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
  onEdit: (role: Role) => void;
  onDelete: (role: Role) => void;
}): ColumnDef<Role>[] {
  return [
    {
      accessorKey: "name",
      header: renderSortableHeader("Role Name"),
      enableSorting: true,
      size: 24,
      cell: ({ row }) =>
        h("div", { class: "font-medium text-sm" }, row.getValue("name")),
    },
    {
      accessorKey: "guardName",
      header: "Guard",
      size: 12,
      cell: ({ row }) =>
        h(
          "div",
          { class: "text-muted-foreground text-xs font-mono" },
          row.getValue("guardName"),
        ),
    },
    {
      accessorKey: "permissions",
      header: "Permissions",
      size: 35,
      cell: ({ row }) => {
        const permissions = row.getValue("permissions") as string[];
        const displayCount = 3;
        const remaining = permissions.length - displayCount;

        return h(
          "div",
          { class: "flex gap-1 flex-wrap items-center" },
          [
            ...permissions.slice(0, displayCount).map((permission) =>
              h(
                Badge,
                {
                  variant: "outline",
                  class:
                    "text-[10px] px-1.5 py-0 h-5 whitespace-nowrap bg-slate-50 text-slate-600 border-slate-200",
                },
                () => permission,
              ),
            ),
            remaining > 0 &&
              h(
                Badge,
                {
                  variant: "outline",
                  class:
                    "text-[10px] px-1.5 py-0 h-5 whitespace-nowrap bg-white text-muted-foreground",
                },
                () => `+${remaining} more`,
              ),
          ].filter(Boolean),
        );
      },
      enableSorting: false,
    },
    {
      accessorKey: "createdAt",
      header: renderSortableHeader("Created At"),
      enableSorting: true,
      size: 18,
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
      size: 11,
      cell: ({ row }) =>
        h(DataTableDropdown, {
          role: row.original,
          onEdit: handlers.onEdit,
          onDelete: handlers.onDelete,
        }),
    },
  ];
}
