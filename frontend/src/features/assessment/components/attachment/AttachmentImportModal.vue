<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { attachmentApi } from "../../api/attachmentApi";
import type { Attachment } from "../../types/attachment.types";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Badge } from "@/components/ui/badge";
import {
  Search,
  FileText,
  Link as LinkIcon,
  Loader2,
  Download,
  CheckCircle2,
} from "lucide-vue-next";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";

/**
 * AttachmentImportModal - High-density data table for browsing and importing attachments
 *
 * Features:
 * - Wide-format layout (max-w-5xl) with explicit width overrides
 * - High-density table with compact padding and refined typography
 * - Full row-click selection synchronized with checkboxes
 * - Sticky header with real-time search filter
 * - Sticky footer for primary actions
 * - Already-linked attachments shown as disabled with checkmarks
 * - Multi-select support for bulk linking
 */

interface Props {
  open: boolean;
  responseId: string;
  assessmentId?: string;
  multiSelect?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  multiSelect: true,
});

const emit = defineEmits<{
  "update:open": [value: boolean];
  linked: [];
}>();

// ============ STATE ============

const searchQuery = ref("");
const attachments = ref<Attachment[]>([]);
const alreadyLinkedIds = ref<string[]>([]);
const selectedIds = ref<Set<string>>(new Set());
const loading = ref(false);
const linking = ref(false);

// ============ COMPUTED ============

const filteredAttachments = computed(() => {
  if (!searchQuery.value.trim()) {
    return attachments.value;
  }

  const query = searchQuery.value.toLowerCase();
  return attachments.value.filter(
    (attachment) =>
      attachment.name.toLowerCase().includes(query) ||
      attachment.description?.toLowerCase().includes(query) ||
      attachment.category?.toLowerCase().includes(query)
  );
});

const hasSelection = computed(() => selectedIds.value.size > 0);

const selectedCount = computed(() => selectedIds.value.size);

// ============ METHODS ============

async function loadAttachments() {
  loading.value = true;
  try {
    const [attachmentList, linkedAttachments] = await Promise.all([
      attachmentApi.listAttachments({
        perPage: 100,
      }),
      attachmentApi.getResponseAttachments(props.responseId),
    ]);

    attachments.value = attachmentList.data;
    alreadyLinkedIds.value = linkedAttachments.map((a) => a.id);
  } catch (error) {
    toast.error(getApiErrorMessage(error, "Failed to load attachments"));
  } finally {
    loading.value = false;
  }
}

function isLinked(attachmentId: string): boolean {
  return alreadyLinkedIds.value.includes(attachmentId);
}

function isSelected(attachmentId: string): boolean {
  return selectedIds.value.has(attachmentId);
}

function toggleSelection(attachmentId: string) {
  if (isLinked(attachmentId)) return;

  if (props.multiSelect) {
    if (selectedIds.value.has(attachmentId)) {
      selectedIds.value.delete(attachmentId);
    } else {
      selectedIds.value.add(attachmentId);
    }
  } else {
    selectedIds.value.clear();
    selectedIds.value.add(attachmentId);
  }
}

function handleRowClick(attachment: Attachment) {
  toggleSelection(attachment.id);
}

async function handleLink() {
  if (selectedIds.value.size === 0) return;

  linking.value = true;
  try {
    const linkPromises = Array.from(selectedIds.value).map((attachmentId) =>
      attachmentApi.linkAttachment({
        assessmentResponseId: props.responseId,
        attachmentId,
      })
    );

    await Promise.all(linkPromises);

    // Refresh linked attachments
    const linkedAttachments = await attachmentApi.getResponseAttachments(
      props.responseId
    );
    alreadyLinkedIds.value = linkedAttachments.map((a) => a.id);

    selectedIds.value.clear();
    toast.success(
      `Successfully linked ${linkPromises.length} attachment${linkPromises.length > 1 ? "s" : ""}`
    );
    emit("linked");
  } catch (error) {
    toast.error(getApiErrorMessage(error, "Failed to link attachments"));
  } finally {
    linking.value = false;
  }
}

function handleClose() {
  emit("update:open", false);
  selectedIds.value.clear();
  searchQuery.value = "";
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
}

function formatDate(dateString: string | undefined | null): string {
  if (!dateString) return "-";
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "-";
    return new Intl.DateTimeFormat("id-ID", {
      year: "numeric",
      month: "short",
      day: "numeric",
    }).format(date);
  } catch {
    return "-";
  }
}

function getCategoryColor(
  category: string | null
): "default" | "secondary" | "outline" {
  const colors: Record<string, "default" | "secondary" | "outline"> = {
    evidence: "default",
    documentation: "secondary",
    reference_material: "outline",
  };
  return category ? colors[category] || "outline" : "outline";
}

function getCategoryLabel(category: string | null): string {
  const labels: Record<string, string> = {
    evidence: "Evidence",
    documentation: "Documentation",
    reference_material: "Reference",
  };
  return category ? labels[category] || category : "Uncategorized";
}

function handleDownload(attachment: Attachment, event: Event) {
  event.stopPropagation();
  window.open(attachment.downloadUrl, "_blank");
}

// ============ WATCHERS ============

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      loadAttachments();
    } else {
      selectedIds.value.clear();
      searchQuery.value = "";
    }
  }
);
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <!-- Explicit width override for max-w-5xl constraint -->
    <DialogContent
      class="!max-w-5xl w-screen h-[85vh] p-0 gap-0 flex flex-col overflow-hidden"
    >
      <!-- ============ STICKY HEADER ============ -->
      <div
        class="sticky top-0 z-20 bg-card border-b px-5 py-3.5 shrink-0 space-y-3"
      >
        <DialogHeader class="space-y-1.5">
          <DialogTitle class="text-lg font-bold tracking-tight">
            Browse Attachment Library
          </DialogTitle>
          <DialogDescription class="text-xs leading-relaxed">
            Select attachments from your organization's library to link to this
            response.
          </DialogDescription>
        </DialogHeader>

        <!-- Search Bar -->
        <div class="relative">
          <Search
            class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none"
          />
          <Input
            v-model="searchQuery"
            placeholder="Search by name, description, or category..."
            class="pl-8 h-8 text-xs bg-background/50"
          />
        </div>

        <!-- Stats Bar -->
        <div class="flex items-center gap-3 text-[10px] text-muted-foreground">
          <span class="font-medium">
            {{ filteredAttachments.length }} of {{ attachments.length }}
            attachments
          </span>
          <span
            v-if="selectedCount > 0"
            class="text-primary font-semibold px-1.5 py-0.5 bg-primary/10 rounded"
          >
            {{ selectedCount }} selected
          </span>
          <span v-if="alreadyLinkedIds.length > 0" class="text-green-600">
            {{ alreadyLinkedIds.length }} already linked
          </span>
        </div>
      </div>

      <!-- ============ TABLE CONTENT - SCROLLABLE ============ -->
      <ScrollArea class="flex-1 min-h-0 px-5">
        <!-- Loading State -->
        <div
          v-if="loading"
          class="flex items-center justify-center py-16 text-muted-foreground"
        >
          <Loader2 class="h-7 w-7 animate-spin" />
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredAttachments.length === 0"
          class="flex flex-col items-center justify-center py-16 text-center"
        >
          <FileText class="h-10 w-10 text-muted-foreground/40 mb-2.5" />
          <p class="text-xs font-semibold text-muted-foreground">
            {{
              searchQuery
                ? "No attachments found matching your search"
                : "No attachments in library"
            }}
          </p>
          <p v-if="searchQuery" class="text-[10px] text-muted-foreground mt-0.5">
            Try adjusting your search terms
          </p>
        </div>

        <!-- High-Density Data Table -->
        <Table v-else class="text-xs">
          <TableHeader class="sticky top-0 bg-muted/80 backdrop-blur-sm z-10">
            <TableRow class="hover:bg-transparent border-b">
              <TableHead class="w-8 h-7 py-1 text-center">
                <span class="sr-only">Select</span>
              </TableHead>
              <TableHead class="h-7 py-1 font-bold text-foreground/90">
                Name
              </TableHead>
              <TableHead class="h-7 py-1 w-24 font-bold text-foreground/90">
                Category
              </TableHead>
              <TableHead
                class="h-7 py-1 w-20 text-right font-bold text-foreground/90"
              >
                Size
              </TableHead>
              <TableHead class="h-7 py-1 w-24 font-bold text-foreground/90">
                Uploaded
              </TableHead>
              <TableHead
                class="h-7 py-1 w-20 text-center font-bold text-foreground/90"
              >
                Status
              </TableHead>
              <TableHead
                class="h-7 py-1 w-12 text-right font-bold text-foreground/90"
              >
                <span class="sr-only">Actions</span>
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="attachment in filteredAttachments"
              :key="attachment.id"
              :class="[
                'cursor-pointer transition-all duration-150 border-b border-border/40',
                isLinked(attachment.id)
                  ? 'bg-muted/20 cursor-not-allowed opacity-50'
                  : isSelected(attachment.id)
                    ? 'bg-primary/8 hover:bg-primary/12 border-primary/20'
                    : 'hover:bg-muted/40',
              ]"
              @click="handleRowClick(attachment)"
            >
              <TableCell
                class="py-1.5 pr-0 text-center"
                @click.stop
              >
                <input
                  type="checkbox"
                  :checked="isLinked(attachment.id) || isSelected(attachment.id)"
                  :disabled="isLinked(attachment.id)"
                  @change="() => toggleSelection(attachment.id)"
                  class="h-3.5 w-3.5 cursor-pointer"
                />
              </TableCell>
              <TableCell class="py-1.5 font-medium">
                <div class="flex flex-col gap-0.5 min-w-0">
                  <span
                    class="truncate max-w-sm text-foreground"
                    :title="attachment.name"
                  >
                    {{ attachment.name }}
                  </span>
                  <span
                    v-if="attachment.description"
                    class="text-[10px] text-muted-foreground/80 truncate max-w-sm leading-tight"
                    :title="attachment.description"
                  >
                    {{ attachment.description }}
                  </span>
                </div>
              </TableCell>
              <TableCell class="py-1.5">
                <Badge
                  :variant="getCategoryColor(attachment.category)"
                  class="text-[9px] px-1.5 py-0 h-4 font-semibold uppercase tracking-wide"
                >
                  {{ getCategoryLabel(attachment.category) }}
                </Badge>
              </TableCell>
              <TableCell class="py-1.5 text-right tabular-nums font-mono">
                {{ formatFileSize(attachment.size) }}
              </TableCell>
              <TableCell class="py-1.5 text-muted-foreground">
                {{ formatDate(attachment.createdAt) }}
              </TableCell>
              <TableCell class="py-1.5 text-center">
                <Badge
                  v-if="isLinked(attachment.id)"
                  variant="outline"
                  class="text-[9px] px-1.5 py-0 h-4 gap-0.5 border-green-600/30 text-green-700 bg-green-50 dark:bg-green-950 dark:text-green-300"
                >
                  <CheckCircle2 class="h-2.5 w-2.5" />
                  Linked
                </Badge>
              </TableCell>
              <TableCell class="py-1.5 text-right">
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-6 w-6 p-0 hover:bg-primary/10"
                  @click="(e: Event) => handleDownload(attachment, e)"
                >
                  <Download class="h-3 w-3" />
                  <span class="sr-only">Download</span>
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </ScrollArea>

      <!-- ============ STICKY FOOTER ============ -->
      <DialogFooter
        class="sticky bottom-0 z-20 bg-card border-t px-5 py-3 shrink-0 flex-row items-center justify-between"
      >
        <div class="text-[10px] text-muted-foreground font-medium">
          <span v-if="props.multiSelect">
            Click rows or checkboxes to select multiple attachments
          </span>
          <span v-else>Click a row to select an attachment</span>
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            class="h-8 text-xs"
            @click="handleClose"
            :disabled="linking"
          >
            Cancel
          </Button>
          <Button
            size="sm"
            class="h-8 text-xs min-w-[100px]"
            @click="handleLink"
            :disabled="!hasSelection || linking"
          >
            <Loader2 v-if="linking" class="h-3.5 w-3.5 animate-spin mr-1.5" />
            <LinkIcon v-else class="h-3.5 w-3.5 mr-1.5" />
            Link {{ selectedCount > 0 ? `(${selectedCount})` : "" }}
          </Button>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
