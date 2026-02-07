import { h } from "vue";
import type { ColumnDef } from "@tanstack/vue-table";
import type { Attachment } from "@/features/assessment/types/attachment.types";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  FileText,
  Download,
  Trash2,
  MoreVertical,
  ArrowUpDown,
  ChevronDown,
  ChevronUp,
} from "lucide-vue-next";

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

interface ActionHandlers {
  onDownload: (attachment: Attachment) => void;
  onDelete: (attachment: Attachment) => void;
}

export function createColumns(
  handlers: ActionHandlers,
): ColumnDef<Attachment>[] {
  return [
    {
      accessorKey: "name",
      header: renderSortableHeader("Attachment"),
      enableSorting: true,
      size: 35,
      cell: ({ row }) => {
        const attachment = row.original;
        return h("div", { class: "flex items-center gap-2 min-w-0" }, [
          h(FileText, { class: "h-4 w-4 shrink-0 text-muted-foreground" }),
          h("div", { class: "flex flex-col min-w-0" }, [
            h(
              "span",
              {
                class: "font-medium text-sm truncate",
                title: attachment.name,
              },
              attachment.name,
            ),
            h(
              "span",
              {
                class: "text-[10px] text-muted-foreground truncate",
                title: attachment.description || "No description",
              },
              attachment.description || "No description",
            ),
          ]),
        ]);
      },
    },
    {
      accessorKey: "category",
      header: "Category",
      size: 15,
      cell: ({ row }) => {
        const category = row.getValue("category") as string;
        const variant = getCategoryVariant(category);
        return h(
          Badge,
          {
            variant,
            class: "text-[10px] px-1.5 py-0 h-5 whitespace-nowrap uppercase tracking-wider",
          },
          () => getCategoryLabel(category),
        );
      },
    },
    {
      accessorKey: "size",
      header: "Size",
      size: 10,
      cell: ({ row }) => {
        const size = row.getValue("size") as number;
        return h(
          "span",
          { class: "text-xs text-muted-foreground" },
          formatFileSize(size),
        );
      },
    },
    {
      accessorKey: "createdByName",
      header: "Uploaded By",
      size: 15,
      cell: ({ row }) =>
        h(
          "span",
          { class: "text-xs text-muted-foreground" },
          row.getValue("createdByName") || "System",
        ),
    },
    {
      accessorKey: "createdAt",
      header: renderSortableHeader("Date"),
      enableSorting: true,
      size: 15,
      cell: ({ row }) =>
        h(
          "span",
          { class: "text-xs text-muted-foreground" },
          formatDate(row.getValue("createdAt")),
        ),
    },
    {
      id: "actions",
      size: 10,
      cell: ({ row }) => {
        const attachment = row.original;
        return h("div", { class: "text-right" }, [
          h(
            DropdownMenu,
            {},
            {
              default: () => [
                h(
                  DropdownMenuTrigger,
                  { asChild: true },
                  {
                    default: () =>
                      h(
                        Button,
                        { variant: "ghost", size: "icon", class: "h-8 w-8" },
                        {
                          default: () =>
                            h(MoreVertical, { class: "h-4 w-4" }),
                        },
                      ),
                  },
                ),
                h(
                  DropdownMenuContent,
                  { align: "end" },
                  {
                    default: () => [
                      h(
                        DropdownMenuItem,
                        { onClick: () => handlers.onDownload(attachment) },
                        {
                          default: () => [
                            h(Download, { class: "mr-2 h-4 w-4" }),
                            h("span", "Download"),
                          ],
                        },
                      ),
                      h(
                        DropdownMenuItem,
                        {
                          class: "text-destructive focus:text-destructive",
                          onClick: () => handlers.onDelete(attachment),
                        },
                        {
                          default: () => [
                            h(Trash2, { class: "mr-2 h-4 w-4" }),
                            h("span", "Delete"),
                          ],
                        },
                      ),
                    ],
                  },
                ),
              ],
            },
          ),
        ]);
      },
    },
  ];
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
}

function formatDate(dateString: string): string {
  if (!dateString) return "-";
  return new Intl.DateTimeFormat("en-US", {
    dateStyle: "short",
    timeStyle: "short",
  }).format(new Date(dateString));
}

function getCategoryLabel(category: string | undefined | null): string {
  if (!category) return "Uncategorized";
  return category
    .split("_")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

function getCategoryVariant(
  category: string | undefined | null,
): "default" | "secondary" | "outline" {
  switch (category) {
    case "evidence":
      return "default";
    case "documentation":
      return "secondary";
    case "reference_material":
      return "outline";
    default:
      return "outline";
  }
}
