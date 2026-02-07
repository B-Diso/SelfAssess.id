import type { ColumnDef } from "@tanstack/table-core";
import type { User } from "@/features/user/types/user.types";
import { h } from "vue";
import { Badge } from "@/components/ui/badge";
import { ArrowUpDown, ChevronDown, ChevronUp } from "lucide-vue-next";
import DataTableDropdown from "./data-table-dropdown.vue";
import { ROLES } from "@/lib/constants";

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

function getRoleDisplay(role: string) {
  switch (role) {
    case ROLES.SUPER_ADMIN:
      return {
        label: "Super Admin",
        class:
          "bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100",
      };
    case ROLES.ORGANIZATION_ADMIN:
      return {
        label: "Org Admin",
        class:
          "bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100",
      };
    case ROLES.ORGANIZATION_USER:
      return {
        label: "Org User",
        class:
          "bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200",
      };
    default:
      return {
        label: role,
        class: "bg-gray-100 text-gray-700 border-gray-200",
      };
  }
}

export function createColumns(
  handlers: {
    onEdit: (user: User) => void;
    onDelete: (user: User) => void;
    onAssignRole: (user: User) => void;
  },
  options?: { showOrganization?: boolean }
): ColumnDef<User>[] {
  const showOrganization = options?.showOrganization !== false; // default true

  const columns: ColumnDef<User>[] = [
    {
      accessorKey: "name",
      header: renderSortableHeader("User"),
      enableSorting: true,
      size: 28,
      cell: ({ row }) => {
        const name = row.getValue("name") as string;
        const email = row.original.email as string;
        return h("div", { class: "flex flex-col" }, [
          h("div", { class: "font-medium text-sm" }, name),
          h("div", { class: "text-xs text-muted-foreground lowercase" }, email),
        ]);
      },
    },
  ];

  if (showOrganization) {
    columns.push({
      accessorKey: "organizationName",
      id: "organizationName",
      header: renderSortableHeader("Organization"),
      enableSorting: true,
      size: 22,
      cell: ({ row }) =>
        h("div", { class: "text-sm" }, row.getValue("organizationName")),
    });
  }

  columns.push(
    {
      accessorKey: "roles",
      header: "Roles",
      size: showOrganization ? 22 : 28,
      cell: ({ row }) => {
        const rolesData = row.getValue("roles");
        const roles = Array.isArray(rolesData)
          ? rolesData
          : typeof rolesData === "string"
            ? rolesData.split(",").map((r) => r.trim())
            : [];
        return h(
          "div",
          { class: "flex gap-1.5 flex-wrap" },
          roles.map((role) => {
            const display = getRoleDisplay(role);
            return h(
              Badge,
              {
                variant: "outline",
                class: `text-[10px] uppercase tracking-wider px-1.5 py-0 h-5 whitespace-nowrap font-bold transition-colors ${display.class}`,
              },
              () => display.label,
            );
          }),
        );
      },
      enableSorting: false,
    },
    {
      accessorKey: "createdAt",
      id: "createdAt",
      header: renderSortableHeader("Created At"),
      enableSorting: true,
      size: showOrganization ? 16 : 22,
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
      size: 12,
      cell: ({ row }) =>
        h(DataTableDropdown, {
          user: row.original,
          onEdit: handlers.onEdit,
          onDelete: handlers.onDelete,
          onAssignRole: handlers.onAssignRole,
        }),
    }
  );

  return columns;
}
