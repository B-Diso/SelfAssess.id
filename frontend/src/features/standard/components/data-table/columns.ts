import type { ColumnDef } from "@tanstack/vue-table";
import type { OrganizationByStandard } from "@/features/standard/types/standard";
import { h } from "vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Progress } from "@/components/ui/progress";
import { ArrowUpDown, ChevronDown, ChevronUp, Eye } from "lucide-vue-next";

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

function getStatusDisplay(status: string | null) {
  switch (status?.toLowerCase()) {
    case null:
    case undefined:
    case "not started":
      return {
        label: "Not Started",
        class: "bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200",
      };
    case "draft":
      return {
        label: "Draft",
        class: "bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200",
      };
    case "active":
      return {
        label: "Active",
        class: "bg-blue-100 text-blue-700 border-blue-200 hover:bg-blue-200",
      };
    case "submitted":
      return {
        label: "Submitted",
        class:
          "bg-yellow-100 text-yellow-700 border-yellow-200 hover:bg-yellow-200",
      };
    case "reviewed":
      return {
        label: "Reviewed",
        class:
          "bg-purple-100 text-purple-700 border-purple-200 hover:bg-purple-200",
      };
    case "finished":
      return {
        label: "Finished",
        class:
          "bg-green-100 text-green-700 border-green-200 hover:bg-green-200",
      };
    case "rejected":
      return {
        label: "Rejected",
        class: "bg-red-100 text-red-700 border-red-200 hover:bg-red-200",
      };
    case "cancelled":
      return {
        label: "Cancelled",
        class: "bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200",
      };
    default:
      return {
        label: status || "Not Started",
        class: "bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200",
      };
  }
}

export function createColumns(handlers: {
  onView: (assessmentId: string) => void;
}): ColumnDef<OrganizationByStandard>[] {
  return [
    {
      accessorKey: "name",
      header: renderSortableHeader("Organization"),
      enableSorting: true,
      cell: ({ row }) =>
        h("div", { class: "font-medium text-sm" }, row.getValue("name")),
    },
    {
      accessorKey: "startedAt",
      id: "startedAt",
      header: renderSortableHeader("Started"),
      enableSorting: true,
      cell: ({ row }) => {
        const startedAt = row.getValue("startedAt") as string | null;
        if (!startedAt) {
          return h("div", { class: "text-muted-foreground" }, "-");
        }
        const date = new Date(startedAt);
        return h("div", { class: "text-sm" }, date.toLocaleDateString());
      },
    },
    {
      accessorKey: "assessmentProgress",
      id: "assessmentProgress",
      header: renderSortableHeader("Progress"),
      enableSorting: true,
      size: 150,
      cell: ({ row }) => {
        const progress = row.getValue("assessmentProgress") as number | null;
        if (progress === null || progress === undefined) {
          return h("div", { class: "text-muted-foreground" }, "-");
        }
        return h("div", { class: "flex items-center gap-2 w-32" }, [
          h(Progress, {
            modelValue: progress,
            max: 100,
            class: "h-2",
          }),
          h(
            "span",
            { class: "text-xs text-muted-foreground w-10" },
            `${progress}%`,
          ),
        ]);
      },
    },
    {
      accessorKey: "assessmentStatus",
      id: "assessmentStatus",
      header: renderSortableHeader("Status"),
      enableSorting: true,
      size: 80,
      cell: ({ row }) => {
        const status = row.getValue("assessmentStatus") as string | null;
        const display = getStatusDisplay(status);
        return h(
          Badge,
          {
            variant: "outline",
            class: `text-[10px] uppercase tracking-wider px-1.5 py-0 h-5 whitespace-nowrap font-bold transition-colors ${display.class}`,
          },
          () => display.label,
        );
      },
    },
    {
      id: "actions",
      enableHiding: false,
      cell: ({ row }) => {
        const organization = row.original;
        if (!organization.hasAssessment || !organization.assessmentId) {
          return null;
        }
        return h("div", { class: "flex justify-end" }, [
          h(
            Button,
            {
              variant: "ghost",
              size: "sm",
              class: "h-8 w-8 p-0",
              onClick: () => handlers.onView(organization.assessmentId!),
            },
            () => h(Eye, { class: "h-4 w-4" }),
          ),
        ]);
      },
    },
  ];
}
