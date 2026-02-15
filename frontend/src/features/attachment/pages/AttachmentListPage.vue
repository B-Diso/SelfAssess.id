<script setup lang="ts">
import { ref, computed } from "vue";
import { watchDebounced } from "@vueuse/core";
import {
  useAttachments,
  useDeleteAttachment,
} from "../composables/useAttachments";
import type {
  Attachment,
  AttachmentFilters,
} from "@/features/assessment/types/attachment.types";
import { Plus, Loader2, FileText } from "lucide-vue-next";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";
import { apiClient } from "@/lib/api/client";
import AttachmentUploadDialog from "@/features/assessment/components/attachment/AttachmentUploadDialog.vue";
import DataTable from "@/components/ui/DataTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import TableToolbar from "@/components/ui/TableToolbar.vue";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { createColumns } from "../components/columns";

// ============ STATE ============

const searchInput = ref("");
const filters = ref<AttachmentFilters>({
  page: 1,
  perPage: 50,
  search: "",
  category: undefined,
});

watchDebounced(
  searchInput,
  (newValue) => {
    filters.value.search = newValue;
    filters.value.page = 1;
  },
  { debounce: 300 },
);

const isUploadDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const attachmentToDelete = ref<Attachment | null>(null);

// ============ QUERIES & MUTATIONS ============

const { data: attachmentData, isLoading, refetch } = useAttachments(filters);
const { mutate: deleteAttachment, isPending: isDeleting } =
  useDeleteAttachment();

// ============ COMPUTED ============

const attachments = computed(() => (attachmentData.value as any)?.data || []);
const totalItems = computed(
  () => (attachmentData.value as any)?.meta?.total || 0,
);
const totalPages = computed(
  () => (attachmentData.value as any)?.meta?.lastPage || 1,
);

const tableColumns = computed(() =>
  createColumns({
    onDownload: handleDownload,
    onDelete: confirmDelete,
  }),
);

// ============ METHODS ============

async function handleDownload(attachment: Attachment) {
  try {
    toast.loading("Downloading file...", { id: `download-${attachment.id}` });
    
    const response = await apiClient.get(`/attachments/${attachment.id}/download`, {
      responseType: "blob",
    });
    // Create download link from blob data
    const blob = response.data as Blob;
    const downloadUrl = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = downloadUrl;
    link.download = attachment.name;
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(downloadUrl);
    toast.success("Download started", { id: `download-${attachment.id}` });
  } catch(error) {
    toast.error(getApiErrorMessage(error, "Failed to download attachment"), {
      id: `download-${attachment.id}`,
    });
  }
}

function confirmDelete(attachment: Attachment) {
  attachmentToDelete.value = attachment;
  isDeleteDialogOpen.value = true;
}

function handleDelete() {
  if (!attachmentToDelete.value) return;

  deleteAttachment(attachmentToDelete.value.id, {
    onSuccess: () => {
      toast.success("Attachment deleted successfully");
      isDeleteDialogOpen.value = false;
      attachmentToDelete.value = null;
      refetch();
    },
    onError: (error) => {
      toast.error(getApiErrorMessage(error, "Failed to delete attachment"));
    },
  });
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
}

function handlePageChange(page: number) {
  filters.value.page = page;
}

function handlePageSizeChange(size: number) {
  filters.value.perPage = size;
  filters.value.page = 1;
}
</script>

<template>
  <div class="h-full flex flex-col overflow-hidden">
    <PageHeader
      title="Attachments"
      description="Library of all uploaded documents, images, and evidence files."
    >
      <template #actions>
        <Button size="sm" class="h-8" @click="isUploadDialogOpen = true">
          <Plus class="h-3.5 w-3.5 mr-2" />
          Upload
        </Button>
      </template>
    </PageHeader>

    <div
      class="flex-1 flex flex-col rounded-lg border shadow-sm overflow-hidden bg-white"
    >
      <TableToolbar
        v-model:search-value="searchInput"
        search-placeholder="Search attachments..."
        :show-reset="searchInput !== ''"
        @reset="
          searchInput = '';
          filters.page = 1;
        "
      />

      <!-- Table Section -->
      <div class="flex-1 overflow-hidden p-3 pt-2">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center h-full">
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="h-8 w-8 animate-spin text-primary" />
            <p
              class="text-xs text-muted-foreground font-medium uppercase tracking-wider"
            >
              Loading attachments...
            </p>
          </div>
        </div>

        <!-- Table -->
        <DataTable
          v-else
          :columns="tableColumns"
          :data="attachments"
          :page="filters.page"
          :page-size="filters.perPage"
          :total-items="totalItems"
          :total-pages="totalPages"
          :compact="true"
          @page-change="handlePageChange"
          @page-size-change="handlePageSizeChange"
        />
      </div>
    </div>

    <!-- Upload Dialog -->
    <AttachmentUploadDialog
      v-model:open="isUploadDialogOpen"
      @uploaded="() => refetch()"
      @uploaded-and-linked="() => refetch()"
    />

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="isDeleteDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Attachment</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this attachment? This action cannot
            be undone.
          </DialogDescription>
        </DialogHeader>
        <div
          v-if="attachmentToDelete"
          class="flex items-center gap-3 p-3 bg-muted rounded-lg"
        >
          <FileText class="h-8 w-8 shrink-0 text-muted-foreground" />
          <div class="flex flex-col min-w-0">
            <span class="font-medium text-sm truncate">{{
              attachmentToDelete.name
            }}</span>
            <span class="text-xs text-muted-foreground">{{
              formatFileSize(attachmentToDelete.size)
            }}</span>
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="isDeleteDialogOpen = false"
            :disabled="isDeleting"
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            @click="handleDelete"
            :disabled="isDeleting"
          >
            <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
