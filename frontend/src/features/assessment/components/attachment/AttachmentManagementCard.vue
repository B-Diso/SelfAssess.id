<script setup lang="ts">
import { ref } from "vue";
import type { Attachment } from "../../types/attachment.types";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardDescription,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { FileText } from "lucide-vue-next";
import AttachmentLinkedTab from "./AttachmentLinkedTab.vue";

interface Props {
  responseId: string;
  assessmentId: string;
  isEditable: boolean;
}

defineProps<Props>();
const emit = defineEmits<{
  openUpload: [];
}>();

const attachmentLinkedTabRef = ref<InstanceType<
  typeof AttachmentLinkedTab
> | null>(null);
const linkedAttachments = ref<Attachment[]>([]);

// Expose method to refresh linked attachments
function refreshLinkedAttachments() {
  attachmentLinkedTabRef.value?.loadLinkedAttachments();
}

// Handle updates from linked tab
function handleLinkedAttachmentsUpdate(attachments: Attachment[]) {
  linkedAttachments.value = attachments;
}

defineExpose({
  refreshLinkedAttachments,
});
</script>

<template>
  <Card class="flex-1 flex flex-col">
    <CardHeader class="pb-4 border-b">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="p-2 bg-primary/10 rounded-lg">
            <FileText class="w-5 h-5 text-primary" />
          </div>
          <div>
            <CardTitle class="text-sm font-bold uppercase tracking-tight">
              Attachments
            </CardTitle>
            <CardDescription class="text-[10px]">
              Manage linked evidence
            </CardDescription>
          </div>
        </div>
        <Badge variant="secondary" class="text-[10px] font-bold h-6">
          {{ linkedAttachments.length }} linked
        </Badge>
      </div>
    </CardHeader>

    <CardContent class="flex-1 flex flex-col min-h-0">
      <AttachmentLinkedTab
        ref="attachmentLinkedTabRef"
        :response-id="responseId"
        :is-editable="isEditable"
        @open-upload="emit('openUpload')"
        @attachments-loaded="handleLinkedAttachmentsUpdate"
      />
    </CardContent>
  </Card>
</template>
