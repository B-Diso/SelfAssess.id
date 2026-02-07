<script setup lang="ts">
import { Send, CheckCircle, AlertCircle, Trash2 } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/features/auth/composables/useAuth";
import { PERMISSIONS } from "@/lib/constants";
import type { AssessmentDetail } from "../../types/assessment.types";
import { computed } from "vue";

interface Props {
  assessment: AssessmentDetail;
  isSubmitting?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isSubmitting: false,
});

const emit = defineEmits<{
  submit: [];
  approve: [];
  finalize: [];
  requestChanges: [];
  delete: [];
}>();

const { hasPermission, isSuperAdmin } = useAuth();

// Permission checks
const canReview = computed(() =>
  hasPermission(PERMISSIONS.REVIEW_QUALITY_ASSESSMENTS),
);

// Status checks
const isDraft = computed(() => props.assessment.status === "draft");
const isActive = computed(() => props.assessment.status === "active");
const isPendingReview = computed(() => props.assessment.status === "pending_review");
const isReviewed = computed(() => props.assessment.status === "reviewed");
const isPendingFinish = computed(() => props.assessment.status === "pending_finish");

// Action availability
// Submit: Active status AND (Org User OR Super Admin)
const canSubmit = computed(() => isActive.value);

// Approve: Pending Review status AND Reviewer permission
const canApprove = computed(() => isPendingReview.value && canReview.value);

// Request Changes: Pending Review or Reviewed status AND Reviewer permission
const canRequestChanges = computed(
  () => (isPendingReview.value || isReviewed.value) && canReview.value,
);

// Finalize: Pending Finish status AND Super Admin only
const canFinalize = computed(() => isPendingFinish.value && isSuperAdmin);

// Delete: Draft status only
const canDelete = computed(() => isDraft.value);
</script>

<template>
  <div class="flex items-center gap-2 flex-wrap">
    <!-- Submit for Review (Organization User) -->
    <Button
      v-if="canSubmit"
      type="button"
      @click="emit('submit')"
      :disabled="isSubmitting"
    >
      <Send class="h-4 w-4 mr-2" />
      Submit for Review
    </Button>

    <!-- Approve Review (Reviewer) - transitions from pending_review to reviewed -->
    <Button
      v-if="canApprove"
      type="button"
      @click="emit('approve')"
      :disabled="isSubmitting"
    >
      <CheckCircle class="h-4 w-4 mr-2" />
      Approve Review
    </Button>

    <!-- Request Changes (Reviewer) - reverts to active -->
    <Button
      v-if="canRequestChanges"
      type="button"
      variant="outline"
      @click="emit('requestChanges')"
      :disabled="isSubmitting"
    >
      <AlertCircle class="h-4 w-4 mr-2" />
      Request Changes
    </Button>

    <!-- Finalize Assessment (Super Admin) - transitions from pending_finish to finished -->
    <Button
      v-if="canFinalize"
      type="button"
      @click="emit('finalize')"
      :disabled="isSubmitting"
    >
      <CheckCircle class="h-4 w-4 mr-2" />
      Finalize Assessment
    </Button>

    <!-- Delete (Draft only) -->
    <Button
      v-if="canDelete"
      type="button"
      variant="destructive"
      @click="emit('delete')"
      :disabled="isSubmitting"
    >
      <Trash2 class="h-4 w-4 mr-2" />
      Delete
    </Button>
  </div>
</template>
