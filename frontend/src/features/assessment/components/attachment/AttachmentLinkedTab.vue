<script setup lang="ts">
import { ref, computed, watch, onMounted } from "vue";
import type { Attachment } from "../../types/attachment.types";
import { attachmentApi } from "../../api/attachmentApi";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";
import { apiClient } from "@/lib/api/client";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
  FileText,
  Download,
  Link2Off,
  Upload,
  Loader2,
  Import,
} from "lucide-vue-next";
import AttachmentImportModal from "./AttachmentImportModal.vue";

interface Props {
  responseId: string;
  isEditable: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  openUpload: [];
  attachmentsLoaded: [attachments: Attachment[]];
}>();

const store = useAssessmentStore();

// Check if attachments can be managed (only when response status is 'active')
const canManageAttachments = computed(() => {
  return store.activeResponse?.status === 'active';
});

const isImportModalOpen = ref(false);

const linkedAttachments = ref<Attachment[]>([]);
const linkedAttachmentsLoading = ref(false);

async function loadLinkedAttachments() {
  if (!props.responseId) return;

  linkedAttachmentsLoading.value = true;
  try {
    linkedAttachments.value = await attachmentApi.getResponseAttachments(
      props.responseId,
    );
    emit("attachmentsLoaded", linkedAttachments.value);
  } catch (error) {
    console.error("Failed to load linked attachments:", error);
  } finally {
    linkedAttachmentsLoading.value = false;
  }
}

async function handleUnlinkAttachment(attachmentId: string) {
  try {
    await attachmentApi.unlinkAttachment(props.responseId, attachmentId);
    toast.success("Attachment unlinked");
    loadLinkedAttachments();
  } catch (error) {
    toast.error(getApiErrorMessage(error, "Failed to unlink attachment"));
  }
}

// Load attachments on mount
onMounted(() => {
  loadLinkedAttachments();
});

// Watch for responseId changes and reload attachments
watch(() => props.responseId, (newResponseId, oldResponseId) => {
  if (newResponseId && newResponseId !== oldResponseId) {
    loadLinkedAttachments();
  }
});

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

function getFileIcon(mimeType: string | undefined | null): string {
  if (!mimeType) return "📄";
  if (mimeType.includes("pdf")) return "📕";
  if (mimeType.includes("word") || mimeType.includes("document")) return "📘";
  if (mimeType.includes("excel") || mimeType.includes("spreadsheet"))
    return "📗";
  if (mimeType.includes("image")) return "🖼️";
  return "📄";
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

async function downloadAttachment(attachment: Attachment) {
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

// Load on mount
loadLinkedAttachments();

defineExpose({
  loadLinkedAttachments,
});
</script>

<template>
  <div class="space-y-4 pt-1">
    <div v-if="isEditable" class="flex justify-end gap-1.5 pb-2">
      <Button
        variant="outline"
        size="sm"
        @click="isImportModalOpen = true"
        :disabled="!canManageAttachments"
        class="h-8 text-xs px-3"
      >
        <Import class="w-3.5 h-3.5 mr-1.5" />
        Import
      </Button>
      <Button
        variant="default"
        size="sm"
        @click="emit('openUpload')"
        :disabled="!canManageAttachments"
        class="h-8 text-xs px-3"
      >
        <Upload class="w-3.5 h-3.5 mr-1.5" />
        Upload
      </Button>
    </div>

    <!-- Import Modal -->
    <AttachmentImportModal
      :open="isImportModalOpen"
      :response-id="responseId"
      @update:open="isImportModalOpen = $event"
      @linked="loadLinkedAttachments"
    />

    <ScrollArea class="max-h-[400px]">
      <div class="space-y-2">
        <div
          v-if="linkedAttachmentsLoading"
          class="flex items-center justify-center py-8"
        >
          <Loader2 class="w-6 h-6 animate-spin text-muted-foreground" />
        </div>

        <template v-else-if="linkedAttachments.length > 0">
          <div
            v-for="attachment in linkedAttachments"
            :key="attachment.id"
            class="group relative flex items-center justify-between p-2.5 border rounded-lg bg-card hover:border-primary/50 transition-all"
          >
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <span class="text-xl shrink-0">{{
                getFileIcon(attachment.mimeType)
              }}</span>
              <div class="min-w-0 flex-1">
                <p
                  class="text-[11px] font-bold truncate group-hover:text-primary transition-colors"
                >
                  {{ attachment.name }}
                </p>
                <div
                  class="flex items-center gap-2 text-[9px] text-muted-foreground font-medium mt-0.5"
                >
                  <span>{{ formatFileSize(attachment.size) }}</span>
                  <span class="opacity-50">•</span>
                  <span>{{ formatDate(attachment.createdAt) }}</span>
                  <template v-if="attachment.category">
                    <span class="opacity-50">•</span>
                    <span
                      class="uppercase tracking-wider font-bold text-primary/70"
                      >{{ attachment.category }}</span
                    >
                  </template>
                </div>
                <p
                  v-if="attachment.description"
                  class="text-[10px] text-muted-foreground/80 mt-1 line-clamp-1 italic leading-tight"
                >
                  "{{ attachment.description }}"
                </p>
              </div>
            </div>
            <div
              class="flex items-center gap-0.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <Button
                variant="ghost"
                size="icon"
                class="h-7 w-7"
                @click="downloadAttachment(attachment)"
                title="Download"
              >
                <Download class="w-3.5 h-3.5" />
              </Button>
              <Button
                v-if="isEditable"
                variant="ghost"
                size="icon"
                class="h-7 w-7 text-muted-foreground hover:text-destructive"
                @click="handleUnlinkAttachment(attachment.id)"
                title="Unlink"
              >
                <Link2Off class="w-3.5 h-3.5" />
              </Button>
            </div>
          </div>
        </template>

        <div
          v-else-if="!linkedAttachmentsLoading"
          class="flex flex-col items-center justify-center py-10 border border-dashed rounded-xl bg-muted/20"
        >
          <div class="p-3 bg-muted/50 rounded-full mb-3">
            <FileText class="w-8 h-8 opacity-30" />
          </div>
          <p class="text-xs font-bold text-muted-foreground">
            No attachments linked yet
          </p>
          <p class="text-[10px] text-muted-foreground/60 mt-1">
            Upload files to support this requirement
          </p>
        </div>
      </div>
    </ScrollArea>
  </div>
</template>
