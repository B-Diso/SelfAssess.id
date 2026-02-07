<script setup lang="ts">
import { ref, computed } from "vue";
import type { AssessmentDetail } from "../../types/assessment.types";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { useAuth } from "@/features/auth/composables/useAuth";
import { storeToRefs } from "pinia";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Send,
  CheckCircle2,
  RotateCcw,
  Loader2,
  AlertTriangle,
  Play,
  ArrowLeft,
  FileSearch,
  RefreshCw,
  Ban,
} from "lucide-vue-next";
import { PERMISSIONS } from "@/lib/constants";

const props = defineProps<{
  assessment: AssessmentDetail;
}>();

const emit = defineEmits<{
  activated: [];
  submitted: [];
  backToActive: [];
  reviewed: [];
  finalized: [];
  reactivated: [];
  pendingFinish: [];
  rejected: [];
}>();

const store = useAssessmentStore();
const { workflowLoading, canSubmit, summary } = storeToRefs(store);
const { hasPermission, isSuperAdmin } = useAuth();

// Check if user is a reviewer
const isReviewer = computed(() =>
  hasPermission(PERMISSIONS.REVIEW_QUALITY_ASSESSMENTS)
);

// Calculate progress percentage
const progressPercentage = computed(() => {
  if (!summary.value || summary.value.totalRequirements === 0) return 0;
  return Math.round(
    (summary.value.completedRequirements / summary.value.totalRequirements) * 100
  );
});

// Dialog states
const showRejectDialog = ref(false);
const showFinalizeDialog = ref(false);
const rejectReason = ref("");
const finalizeNote = ref("");

// Status-based computed properties
const isDraft = computed(() => props.assessment.status === "draft");
const isActive = computed(() => props.assessment.status === "active");
const isPendingReview = computed(() => props.assessment.status === "pending_review");
const isReviewed = computed(() => props.assessment.status === "reviewed");
const isPendingFinish = computed(() => props.assessment.status === "pending_finish");
const isFinished = computed(() => props.assessment.status === "finished");
const isRejected = computed(() => props.assessment.status === "rejected");
const isCancelled = computed(() => props.assessment.status === "cancelled");

// Determine if submit for review should be disabled
const isSubmitDisabled = computed(() => {
  return !canSubmit.value || workflowLoading.value || progressPercentage.value < 100;
});

// Get tooltip text for disabled submit button
const submitTooltipText = computed(() => {
  if (progressPercentage.value < 100) {
    return `Progress must be 100% to submit (current: ${progressPercentage.value}%)`;
  }
  if (!canSubmit.value) {
    return "Assessment cannot be submitted at this time";
  }
  return "";
});

// Handler functions

async function handleActivate() {
  if (!confirm("Are you sure you want to activate this assessment?")) return;
  await store.activateAssessment();
  emit("activated");
}

async function handleSubmitForReview() {
  if (!confirm("Are you sure you want to submit this assessment for review?"))
    return;
  await store.submitForReview();
  emit("submitted");
}

async function handleBackToActive() {
  if (!confirm("Return this assessment to active status?")) return;
  await store.backToActive();
  emit("backToActive");
}

async function handleCancelSubmission() {
  if (!confirm("Cancel this submission and return to active status?")) return;
  await store.cancelSubmission();
  emit("backToActive");
}

async function handleRequestFinish() {
  if (!confirm("Request this assessment to be finalized?")) return;
  await store.requestFinish();
  emit("pendingFinish");
}

async function handleCancelFinishRequest() {
  if (!confirm("Cancel the finish request and return to reviewed status?")) return;
  await store.cancelFinishRequest();
  emit("reviewed");
}

async function handleStartReview() {
  if (!confirm("Start reviewing this assessment?")) return;
  await store.startReview();
  emit("reviewed");
}

async function handleReject() {
  await store.requestChanges(rejectReason.value);
  showRejectDialog.value = false;
  rejectReason.value = "";
  emit("backToActive");
}

async function handleFinalize() {
  await store.finalize(finalizeNote.value);
  showFinalizeDialog.value = false;
  finalizeNote.value = "";
  emit("finalized");
}

async function handleReactivate() {
  if (!confirm("Re-activate this cancelled assessment?")) return;
  await store.reactivateAssessment();
  emit("reactivated");
}

function handleViewReport() {
  // TODO: Implement report download/view
  console.log("View report");
}
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- DRAFT STATUS: Activate -->
    <template v-if="isDraft">
      <Button
        size="sm"
        variant="default"
        class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-blue-600 hover:bg-blue-700"
        @click="handleActivate"
        :disabled="workflowLoading"
      >
        <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
        <Play v-else class="w-3.5 h-3.5 mr-2" />
        Activate
      </Button>
    </template>

    <!-- ACTIVE STATUS: Submit for Review -->
    <template v-if="isActive">
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              size="sm"
              variant="default"
              class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider"
              @click="handleSubmitForReview"
              :disabled="isSubmitDisabled"
            >
              <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
              <Send v-else class="w-3.5 h-3.5 mr-2" />
              Submit for Review
              <ArrowLeft v-if="progressPercentage >= 100" class="w-3 h-3 ml-1 rotate-180" />
            </Button>
          </TooltipTrigger>
          <TooltipContent v-if="isSubmitDisabled">
            <p>{{ submitTooltipText }}</p>
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </template>

    <!-- SUPER ADMIN OVERRIDE: Can submit from Draft status -->
    <template v-if="isDraft && isSuperAdmin">
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              size="sm"
              variant="default"
              class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-blue-600 hover:bg-blue-700"
              @click="handleSubmitForReview"
              :disabled="isSubmitDisabled"
            >
              <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
              <Send v-else class="w-3.5 h-3.5 mr-2" />
              Submit (Admin)
              <ArrowLeft v-if="progressPercentage >= 100" class="w-3 h-3 ml-1 rotate-180" />
            </Button>
          </TooltipTrigger>
          <TooltipContent v-if="isSubmitDisabled">
            <p>{{ submitTooltipText }}</p>
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </template>

    <!-- PENDING_REVIEW STATUS:
         - Cancel Submission: Org Admin (peminta)
         - Back to Active + Review: Reviewer
    -->
    <template v-if="isPendingReview">
      <!-- Cancel Submission - Org Admin can cancel their own submission -->
      <Button
        v-if="!isReviewer"
        variant="outline"
        size="sm"
        class="h-9 px-3 font-bold text-[10px] uppercase tracking-wider border-amber-300 text-amber-600 hover:bg-amber-50 hover:text-amber-700"
        @click="handleCancelSubmission"
        :disabled="workflowLoading"
      >
        <ArrowLeft class="w-3.5 h-3.5 mr-2" />
        Cancel Submission
      </Button>

      <!-- Reviewer Actions -->
      <template v-if="isReviewer">
        <Button
          variant="outline"
          size="sm"
          class="h-9 px-3 font-bold text-[10px] uppercase tracking-wider"
          @click="handleBackToActive"
          :disabled="workflowLoading"
        >
          <ArrowLeft class="w-3.5 h-3.5 mr-2" />
          Back to Active
        </Button>
        <Button
          size="sm"
          variant="default"
          class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-indigo-600 hover:bg-indigo-700"
          @click="handleStartReview"
          :disabled="workflowLoading"
        >
          <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
          <FileSearch v-else class="w-3.5 h-3.5 mr-2" />
          Review
          <ArrowLeft class="w-3 h-3 ml-1 rotate-180" />
        </Button>
      </template>
    </template>

    <!-- REVIEWED STATUS:
         - Request Finish: Org Admin (moves to pending_finish)
         - Request Changes: Reviewer (reverts to active)
    -->
    <template v-if="isReviewed">
      <!-- Org Admin: Request Finish -->
      <Button
        v-if="!isReviewer"
        size="sm"
        variant="default"
        class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-indigo-600 hover:bg-indigo-700"
        @click="handleRequestFinish"
        :disabled="workflowLoading"
      >
        <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
        <Send v-else class="w-3.5 h-3.5 mr-2" />
        Request Finish
      </Button>
      
      <!-- Reviewer Actions -->
      <template v-if="isReviewer">
      <!-- Request Changes Dialog -->
      <Dialog v-model:open="showRejectDialog">
        <DialogTrigger as-child>
          <Button
            variant="outline"
            size="sm"
            class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider border-red-300 text-red-600 hover:bg-red-50 hover:text-red-700"
            :disabled="workflowLoading"
          >
            <Ban class="w-3.5 h-3.5 mr-2" />
            Request Changes...
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Request Changes</DialogTitle>
            <DialogDescription>
              Please provide a reason for requesting changes. The assessment will be returned to active status for modifications.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4 py-4">
            <div class="space-y-2">
              <Label>Reason <span class="text-red-500">*</span></Label>
              <Textarea
                v-model="rejectReason"
                placeholder="Enter the reason for requesting changes..."
                :rows="4"
              />
            </div>
          </div>

          <DialogFooter>
            <Button
              variant="outline"
              @click="showRejectDialog = false"
              :disabled="workflowLoading"
            >
              Cancel
            </Button>
            <Button
              variant="destructive"
              @click="handleReject"
              :disabled="workflowLoading || !rejectReason.trim()"
            >
              <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
              <RotateCcw v-else class="w-3.5 h-3.5 mr-2" />
              Request Changes
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      </template>
    </template>

    <!-- PENDING_FINISH STATUS:
         - Cancel Finish Request: Org Admin (peminta)
         - Approve Finish: Super Admin
    -->
    <template v-if="isPendingFinish">
      <!-- Org Admin: Cancel Finish Request -->
      <Button
        v-if="!isSuperAdmin"
        variant="outline"
        size="sm"
        class="h-9 px-3 font-bold text-[10px] uppercase tracking-wider border-amber-300 text-amber-600 hover:bg-amber-50 hover:text-amber-700"
        @click="handleCancelFinishRequest"
        :disabled="workflowLoading"
      >
        <ArrowLeft class="w-3.5 h-3.5 mr-2" />
        Cancel Finish Request
      </Button>

      <!-- Super Admin: Approve Finish -->
      <Button
        v-if="isSuperAdmin"
        size="sm"
        variant="default"
        class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-green-600 hover:bg-green-700"
        @click="showFinalizeDialog = true"
        :disabled="workflowLoading"
      >
        <CheckCircle2 class="w-3.5 h-3.5 mr-2" />
        Approve Finish
      </Button>

      <!-- Finalize Dialog - Only Super Admin can finalize -->
      <Dialog v-if="isSuperAdmin" v-model:open="showFinalizeDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Finish Assessment</DialogTitle>
            <DialogDescription>
              This will mark the assessment as complete and generate the final report.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4 py-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
              <div class="flex items-start gap-3">
                <AlertTriangle class="w-5 h-5 text-yellow-600 shrink-0 mt-0.5" />
                <div class="text-sm text-yellow-800">
                  <p class="font-medium">Important</p>
                  <p>Once finalized, the assessment cannot be modified.</p>
                </div>
              </div>
            </div>

            <div class="space-y-2">
              <Label>Note (optional)</Label>
              <Textarea
                v-model="finalizeNote"
                placeholder="Add any final notes or comments..."
                :rows="4"
              />
            </div>
          </div>

          <DialogFooter>
            <Button
              variant="outline"
              @click="showFinalizeDialog = false"
              :disabled="workflowLoading"
            >
              Cancel
            </Button>
            <Button
              @click="handleFinalize"
              :disabled="workflowLoading"
              class="bg-green-600 hover:bg-green-700"
            >
              <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
              <CheckCircle2 v-else class="w-3.5 h-3.5 mr-2" />
              Finish Now
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </template>

    <!-- REJECTED STATUS: Re-activate -->
    <template v-if="isRejected">
      <Button
        size="sm"
        variant="default"
        class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-blue-600 hover:bg-blue-700"
        @click="handleReactivate"
        :disabled="workflowLoading"
      >
        <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
        <RefreshCw v-else class="w-3.5 h-3.5 mr-2" />
        Re-activate
      </Button>
    </template>

    <!-- FINISHED STATUS: View Report -->
    <template v-if="isFinished">
      <Button
        size="sm"
        variant="outline"
        class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider"
        @click="handleViewReport"
      >
        <FileSearch class="w-3.5 h-3.5 mr-2" />
        View Report
      </Button>
    </template>

    <!-- CANCELLED STATUS: Re-activate -->
    <template v-if="isCancelled">
      <Button
        size="sm"
        variant="default"
        class="h-9 px-4 font-bold text-[10px] uppercase tracking-wider bg-blue-600 hover:bg-blue-700"
        @click="handleReactivate"
        :disabled="workflowLoading"
      >
        <Loader2 v-if="workflowLoading" class="w-3.5 h-3.5 animate-spin mr-2" />
        <RefreshCw v-else class="w-3.5 h-3.5 mr-2" />
        Re-activate
      </Button>
    </template>
  </div>
</template>
