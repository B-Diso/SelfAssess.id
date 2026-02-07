<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Upload, X, File } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { ScrollArea } from "@/components/ui/scroll-area";
import { toast } from "vue-sonner";
import { getApiErrorMessage } from "@/lib/api-error";
import { attachmentApi } from "../../api/attachmentApi";
import type { Attachment } from "../../types/attachment.types";
import {
  validateFile,
  formatFileSize as formatFileSizeUtil,
  getAcceptAttributeValue,
  FILE_TYPE_CATEGORIES,
} from "@/lib/config/upload";

interface Props {
  open: boolean;
  responseId?: string;
  assessmentId?: string;
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
});

const emit = defineEmits<{
  "update:open": [value: boolean];
  uploaded: [attachment: Attachment];
  "uploaded-and-linked": [];
}>();

const isUploading = ref(false);
const selectedFile = ref<File | null>(null);
const description = ref("");
const isDragging = ref(false);

// Upload mode: true = Upload & Link (direct), false = Upload to Library
// When responseId is provided, always use direct upload mode (no toggle)
const uploadDirectly = ref(true);

// File size limits
const directUploadLimit = 20 * 1024 * 1024; // 20MB for direct upload
const libraryUploadLimit = 50 * 1024 * 1024; // 50MB for library

const fileSizeLimit = computed(() =>
  uploadDirectly.value ? directUploadLimit : libraryUploadLimit,
);

const fileSizeLimitText = computed(() =>
  uploadDirectly.value ? "20MB" : "50MB",
);

// Get allowed file types for display
const allowedFileTypes = computed(() => {
  const categories = Object.values(FILE_TYPE_CATEGORIES);
  return categories.map((c) => c.label).join(", ");
});

// Auto-set upload mode based on responseId
// When responseId is provided, force direct upload mode
watch(
  () => props.responseId,
  (newValue) => {
    uploadDirectly.value = !!newValue;
  },
  { immediate: true },
);

const isValidFile = (file: File) => {
  const validation = validateFile(file, fileSizeLimit.value);
  if (!validation.valid) {
    toast.error(validation.error || "File tidak valid");
    return false;
  }
  return true;
};

const handleFileSelect = (file: File) => {
  if (!isValidFile(file)) {
    return;
  }
  selectedFile.value = file;
};

const handleDrop = (e: DragEvent) => {
  e.preventDefault();
  isDragging.value = false;
  const files = e.dataTransfer?.files;
  if (files && files.length > 0 && files[0]) {
    handleFileSelect(files[0]);
  }
};

const handleDragOver = (e: DragEvent) => {
  e.preventDefault();
  isDragging.value = true;
};

const handleDragLeave = () => {
  isDragging.value = false;
};

const handleInputChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const files = target.files;
  if (files && files.length > 0 && files[0]) {
    handleFileSelect(files[0]);
  }
};

const removeFile = () => {
  selectedFile.value = null;
};

const formatFileSize = (bytes: number) => {
  return formatFileSizeUtil(bytes);
};

const canSubmit = computed(() => {
  return selectedFile.value !== null && !isUploading.value;
});

async function handleSubmit() {
  if (!selectedFile.value) return;

  isUploading.value = true;
  try {
    if (uploadDirectly.value && props.responseId) {
      // Upload & Link mode - single API call
      await attachmentApi.uploadAndLinkAttachment({
        assessmentResponseId: props.responseId,
        file: selectedFile.value,
        description: description.value || undefined,
      });
      toast.success("Attachment uploaded and linked successfully");
      emit("uploaded-and-linked");
    } else {
      // Upload to Library mode
      const attachment = await attachmentApi.uploadAttachment({
        file: selectedFile.value,
        category: "evidence",
        description: description.value || undefined,
      });
      toast.success("Attachment uploaded to library");
      emit("uploaded", attachment);
    }
    handleClose();
  } catch (error) {
    toast.error(getApiErrorMessage(error, "Failed to upload attachment"));
  } finally {
    isUploading.value = false;
  }
}

function handleClose() {
  emit("update:open", false);
  // Reset form
  selectedFile.value = null;
  description.value = "";
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent
      class="sm:max-w-[450px] p-0 overflow-hidden"
      @close="handleClose"
    >
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-xl font-bold">Upload Attachment</DialogTitle>
        <DialogDescription class="text-xs">
          Upload a file for compliance assessment evidence.
        </DialogDescription>
      </DialogHeader>

      <ScrollArea class="max-h-[65vh]">
        <div class="px-6 py-4 space-y-4">
          <!-- File Drop Zone -->
          <div
            :class="[
              'border border-dashed rounded-lg p-6 text-center transition-colors',
              isDragging
                ? 'border-primary bg-primary/5'
                : 'border-muted-foreground/25',
              selectedFile
                ? 'border-green-500 bg-green-50/50 dark:bg-green-950/20'
                : 'bg-muted/5',
            ]"
            @drop="handleDrop"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
          >
            <div v-if="!selectedFile" class="space-y-3">
              <div
                class="mx-auto w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center"
              >
                <Upload class="w-5 h-5 text-primary" />
              </div>
              <div>
                <p class="text-sm font-bold">Click or drag file to upload</p>
                <p class="text-[10px] text-muted-foreground mt-0.5">
                  Ukuran maksimal: {{ fileSizeLimitText }}
                </p>
                <p class="text-[10px] text-muted-foreground mt-0.5">
                  Tipe: {{ allowedFileTypes }}
                </p>
              </div>
              <div class="relative">
                <Input
                  type="file"
                  class="absolute inset-0 opacity-0 cursor-pointer h-full"
                  @change="handleInputChange"
                  :accept="getAcceptAttributeValue()"
                />
                <Button
                  variant="outline"
                  size="sm"
                  class="h-8 text-[11px] w-full pointer-events-none"
                >
                  Choose File
                </Button>
              </div>
            </div>

            <div
              v-else
              class="flex items-center justify-between bg-background/50 p-2 rounded-md border border-green-500/20"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <div
                  class="w-8 h-8 rounded bg-green-100 dark:bg-green-900 flex items-center justify-center shrink-0"
                >
                  <File class="w-4 h-4 text-green-600 dark:text-green-400" />
                </div>
                <div class="text-left min-w-0">
                  <p class="text-xs font-bold truncate">
                    {{ selectedFile.name }}
                  </p>
                  <p class="text-[10px] text-muted-foreground">
                    {{ formatFileSize(selectedFile.size) }}
                  </p>
                </div>
              </div>
              <Button
                variant="ghost"
                size="icon"
                @click="removeFile"
                class="h-7 w-7 text-muted-foreground hover:text-destructive shrink-0"
              >
                <X class="w-3.5 h-3.5" />
              </Button>
            </div>
          </div>

          <!-- Description -->
          <div class="space-y-1.5">
            <Label
              for="description"
              class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
              >Description (optional)</Label
            >
            <Textarea
              id="description"
              v-model="description"
              placeholder="What is this file for?..."
              :rows="2"
              class="text-xs resize-none"
            />
          </div>
        </div>
      </ScrollArea>

      <DialogFooter class="bg-muted/30 px-6 py-3 border-t">
        <Button
          variant="ghost"
          size="sm"
          @click="handleClose"
          :disabled="isUploading"
          class="text-xs"
        >
          Cancel
        </Button>
        <Button
          size="sm"
          @click="handleSubmit"
          :disabled="!canSubmit"
          :loading="isUploading"
          class="text-xs px-6"
        >
          {{
            uploadDirectly && responseId ? "Upload & Link" : "Upload to Library"
          }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
