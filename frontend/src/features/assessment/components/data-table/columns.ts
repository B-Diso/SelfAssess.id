import type { ColumnDef } from "@tanstack/vue-table";
import type { Assessment } from "@/features/assessment/types/assessment.types";
import { h } from "vue";
import { Progress } from "@/components/ui/progress";
import { ArrowUpDown, ChevronDown, ChevronUp } from "lucide-vue-next";
import DataTableDropdown from "./data-table-dropdown.vue";
import StatusBadge from "../shared/status-badge.vue";
import TruncatedText from "./TruncatedText.vue";

function renderSortableHeader(label: string, className: string = "") {
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
          `flex items-center cursor-pointer select-none hover:text-foreground transition-colors py-1 px-2 -ml-2 rounded-md hover:bg-muted/50 text-xs font-medium whitespace-nowrap ${className}`,
        onClick: (e: MouseEvent) => {
          e.preventDefault();
          column.toggleSorting(sortState === "asc");
        },
      },
      [h("span", label), icon],
    );
  };
}

export function useAssessmentColumns(handlers: {
  onView: (assessment: Assessment) => void;
  onEdit: (assessment: Assessment) => void;
  onEditDetail?: (assessment: Assessment) => void;
  onDelete: (assessment: Assessment) => void;
  onActivate: (assessment: Assessment) => void;
  onCancel: (assessment: Assessment) => void;
  onSubmit?: (assessment: Assessment) => void;
  onApprove?: (assessment: Assessment) => void;
  onReject?: (assessment: Assessment) => void;
  onRequestFinish?: (assessment: Assessment) => void;
  onFinalize?: (assessment: Assessment) => void;
  onRevert?: (assessment: Assessment) => void;
}): ColumnDef<Assessment>[] {
  return [
    {
      accessorKey: "name",
      header: renderSortableHeader("Assessment"),
      enableSorting: true,
      size: 30,
      minSize: 20,
      maxSize: 40,
      cell: ({ row }) => {
        const assessment = row.original;

        return h("div", { class: "flex flex-col gap-0.5 min-w-0" }, [
          h(TruncatedText, {
            text: assessment.name,
            className: "font-medium text-sm"
          }),
          assessment.organizationName
            ? h(TruncatedText, {
                text: assessment.organizationName,
                className: "text-[10px] text-muted-foreground"
              })
            : null,
        ]);
      },
    },
    {
      accessorKey: "periodValue",
      id: "periodValue",
      header: renderSortableHeader("Period"),
      enableSorting: false,
      size: 15,
      minSize: 12,
      maxSize: 20,
      cell: ({ row }) => {
        const periodValue = row.getValue("periodValue") as string | null | undefined;
        if (!periodValue) {
          return h("span", { class: "text-muted-foreground italic text-xs" }, "-");
        }

        return h(TruncatedText, {
          text: periodValue,
          className: "text-sm"
        });
      },
    },
    {
      accessorKey: "standardName",
      id: "standardName",
      header: renderSortableHeader("Standard"),
      enableSorting: true,
      size: 20,
      minSize: 15,
      maxSize: 25,
      cell: ({ row }) => {
        const standardName = row.getValue("standardName") as string | null;
        if (!standardName) {
          return h("span", { class: "text-muted-foreground italic text-xs" }, "-");
        }

        return h(TruncatedText, {
          text: standardName,
          className: "text-sm"
        });
      },
    },
    {
      accessorKey: "progress",
      id: "progress",
      header: "Progress",
      enableSorting: false,
      size: 12,
      minSize: 10,
      maxSize: 15,
      cell: ({ row }) => {
        const assessment = row.original;
        const percentage = assessment.percentage ?? 0;
        
        return h("div", { class: "flex flex-col gap-1 w-full min-w-0" }, [
          h("div", { class: "flex items-center gap-2" }, [
            h(Progress, { 
              modelValue: percentage,
              class: "h-1.5 flex-1 min-w-0"
            }),
            h(
              "span",
              { class: "text-xs text-muted-foreground whitespace-nowrap" },
              `${percentage}%`
            ),
          ]),
        ]);
      },
    },
    {
      accessorKey: "status",
      header: "Status",
      enableSorting: false,
      size: 16,
      minSize: 12,
      maxSize: 20,
      cell: ({ row }) => {
        const status = row.getValue("status") as Assessment["status"];
        return h(StatusBadge, { status, size: "sm" });
      },
    },
    {
      id: "actions",
      enableHiding: false,
      size: 10,
      minSize: 8,
      maxSize: 12,
      cell: ({ row }) =>
        h(DataTableDropdown, {
          assessment: row.original,
          onView: handlers.onView,
          onEdit: handlers.onEdit,
          onEditDetail: handlers.onEditDetail,
          onDelete: handlers.onDelete,
          onActivate: handlers.onActivate,
          onCancel: handlers.onCancel,
          onSubmit: handlers.onSubmit,
          onApprove: handlers.onApprove,
          onReject: handlers.onReject,
          onRequestFinish: handlers.onRequestFinish,
          onFinalize: handlers.onFinalize,
          onRevert: handlers.onRevert,
        }),
    },
  ];
}

// Keep old function for backward compatibility
export function createColumns(handlers: {
  onView: (assessment: Assessment) => void;
  onEdit: (assessment: Assessment) => void;
  onEditDetail?: (assessment: Assessment) => void;
  onDelete: (assessment: Assessment) => void;
  onActivate: (assessment: Assessment) => void;
  onCancel: (assessment: Assessment) => void;
  onSubmit?: (assessment: Assessment) => void;
  onApprove?: (assessment: Assessment) => void;
  onReject?: (assessment: Assessment) => void;
  onRequestFinish?: (assessment: Assessment) => void;
  onFinalize?: (assessment: Assessment) => void;
  onRevert?: (assessment: Assessment) => void;
}): ColumnDef<Assessment>[] {
  return useAssessmentColumns(handlers);
}
