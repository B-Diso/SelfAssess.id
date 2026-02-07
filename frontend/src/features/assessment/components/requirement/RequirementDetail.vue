<script setup lang="ts">
import { ref } from "vue";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { storeToRefs } from "pinia";
import { Target } from "lucide-vue-next";
import RequirementInfoHeader from "./RequirementInfoHeader.vue";
import RequirementComplianceForm from "./RequirementComplianceForm.vue";
import ActionPlanPanel from "../panels/ActionPlanPanel.vue";
import AttachmentUploadDialog from "../attachment/AttachmentUploadDialog.vue";
import AttachmentManagementCard from "../attachment/AttachmentManagementCard.vue";

interface Props {
  assessmentId: string;
}

defineProps<Props>();

const store = useAssessmentStore();
const { activeResponse, activeNode, isEditable } = storeToRefs(store);

const showAttachmentUploadDialog = ref(false);
const attachmentManagementCardRef = ref<InstanceType<
  typeof AttachmentManagementCard
> | null>(null);
const complianceFormRef = ref<InstanceType<typeof RequirementComplianceForm> | null>(null);

// Handle upload completion
function handleUploadedAndLinked() {
  attachmentManagementCardRef.value?.refreshLinkedAttachments();
}

// Save form data
async function saveForm() {
  await complianceFormRef.value?.handleSubmit();
}

// Expose saveForm to parent
defineExpose({ saveForm });
</script>

<template>
  <div v-if="activeResponse" class="h-full flex flex-col">
    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
      <div class="p-4 space-y-4">
        <!-- Requirement Header -->
        <RequirementInfoHeader :response="activeResponse" />

        <!-- Compliance Form -->
        <RequirementComplianceForm
          ref="complianceFormRef"
          v-if="activeResponse && activeNode"
          :response="activeResponse"
          :requirement="activeNode"
        />

        <!-- Action Plan and Attachment Management - Side by Side -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Action Plan Panel -->
          <ActionPlanPanel
            :response-id="activeResponse.id"
            :assessment-id="assessmentId"
          />

          <!-- Attachment Management -->
          <AttachmentManagementCard
            ref="attachmentManagementCardRef"
            :response-id="activeResponse.id"
            :assessment-id="assessmentId"
            :is-editable="isEditable"
            @open-upload="showAttachmentUploadDialog = true"
          />
        </div>
      </div>
    </div>

    <!-- Attachment Upload Dialog -->
    <AttachmentUploadDialog
      v-model:open="showAttachmentUploadDialog"
      :response-id="activeResponse.id"
      :assessment-id="assessmentId"
      @uploaded-and-linked="handleUploadedAndLinked"
    />
  </div>

  <div v-else class="flex items-center justify-center h-full">
    <div class="text-center">
      <Target class="w-12 h-12 mx-auto mb-4 text-muted-foreground opacity-50" />
      <p class="text-muted-foreground">
        Select a requirement from the sidebar to view details
      </p>
    </div>
  </div>
</template>
