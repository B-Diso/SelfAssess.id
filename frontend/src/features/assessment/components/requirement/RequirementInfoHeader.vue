<script setup lang="ts">
import { ref, computed, watch } from "vue";
import type {
  AssessmentResponse,
  ResponseHistoryItem,
} from "../../types/assessment-response.types";
import { assessmentApi } from "../../api/assessmentApi";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { useUserStore } from "@/features/auth/stores/userStore";
import { storeToRefs } from "pinia";
import { useSanitizeHtml } from "@/composables/useSanitizeHtml";
import { Button } from "@/components/ui/button";
import {
  Lightbulb,
  Info,
  Send,
  CheckCircle2,
  RotateCcw,
  Loader2,
  MoreVertical,
  History,
  AlertTriangle,
} from "lucide-vue-next";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

interface Props {
  response: AssessmentResponse;
}

const props = defineProps<Props>();

const store = useAssessmentStore();
const userStore = useUserStore();
const { workflowLoading } = storeToRefs(store);

const showSubmitDialog = ref(false);
const showRejectDialog = ref(false);
const showRevertDialog = ref(false);
const showHistoryDialog = ref(false);
const transitionNote = ref("");

// History state
const historyItems = ref<ResponseHistoryItem[]>([]);
const historyLoading = ref(false);
const historyError = ref<string | null>(null);

// Fetch history when dialog opens
watch(showHistoryDialog, async (isOpen) => {
  if (isOpen) {
    await fetchHistory();
  }
});

async function fetchHistory() {
  historyLoading.value = true;
  historyError.value = null;
  try {
    const data = await assessmentApi.getResponseHistory(props.response.id);
    historyItems.value = data;
  } catch (error) {
    historyError.value = "Failed to load history";
    console.error("Failed to fetch history:", error);
  } finally {
    historyLoading.value = false;
  }
}

const { sanitize } = useSanitizeHtml();

const statusOptions = {
  active: {
    label: "Active",
    variant: "secondary" as const,
    icon: Info,
    color: "bg-blue-100 text-blue-700",
  },
  pending_review: {
    label: "Pending Review",
    variant: "default" as const,
    icon: Send,
    color: "bg-indigo-100 text-indigo-700",
  },
  reviewed: {
    label: "Reviewed",
    variant: "default" as const,
    icon: CheckCircle2,
    color: "bg-green-100 text-green-700",
  },
};

const currentStatusInfo = computed(() => {
  return (
    statusOptions[props.response.status as keyof typeof statusOptions] ||
    statusOptions.active
  );
});

// Response Workflow: active → pending_review → reviewed
// Only 3 statuses: active (0%), pending_review (50%), reviewed (100%)
const canSubmit = computed(() => props.response.status === "active");
const canApprove = computed(() => props.response.status === "pending_review");
const canRequestChanges = computed(
  () => props.response.status === "pending_review",
);
// Allow Super Admin OR Org Admin to revert reviewed → active (for correcting mistakes)
const canRevertToActive = computed(
  () =>
    props.response.status === "reviewed" &&
    (userStore.isOrganizationAdmin || userStore.isSuperAdmin),
);

async function handleSubmit() {
  await store.transitionResponse(
    props.response.id,
    "pending_review",
    transitionNote.value || undefined,
  );
  showSubmitDialog.value = false;
  transitionNote.value = "";
}

async function handleApprove() {
  await store.transitionResponse(props.response.id, "reviewed");
}

async function handleRequestChanges() {
  // Response workflow: from pending_review back to active (reject/return)
  await store.transitionResponse(
    props.response.id,
    "active",
    transitionNote.value || undefined,
  );
  showRejectDialog.value = false;
  transitionNote.value = "";
}

async function handleRevertToActive() {
  // Response workflow: from reviewed back to active (Org Admin ONLY)
  await store.transitionResponse(
    props.response.id,
    "active",
    transitionNote.value || undefined,
  );
  showRevertDialog.value = false;
  transitionNote.value = "";
}

const complianceStatusOptions = [
  {
    value: "non_compliant",
    label: "Non-Compliant",
    color: "bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-100",
  },
  {
    value: "partially_compliant",
    label: "Partially Compliant",
    color:
      "bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-100",
  },
  {
    value: "fully_compliant",
    label: "Fully Compliant",
    color: "bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-100",
  },
  {
    value: "not_applicable",
    label: "Not Applicable",
    color: "bg-gray-100 text-gray-800 dark:bg-gray-950 dark:text-gray-100",
  },
];

function getComplianceStatusColor(status: string): string {
  return (
    complianceStatusOptions.find((s) => s.value === status)?.color ||
    "bg-gray-100 text-gray-800 dark:bg-gray-950 dark:text-gray-100"
  );
}

function getComplianceStatusLabel(status: string): string {
  return (
    complianceStatusOptions.find((s) => s.value === status)?.label || "Unknown"
  );
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-start justify-between">
      <div class="flex-1 space-y-3">
        <!-- Requirement Code and Compliance Status -->
        <div class="flex items-center gap-3 flex-wrap">
          <div
            class="inline-block px-3 py-1.5 bg-muted/50 text-muted-foreground rounded-md border border-muted-foreground/20"
          >
            <span class="text-[10px] font-mono font-bold">{{
              response.requirementDisplayCode || "N/A"
            }}</span>
          </div>
          <div
            :class="
              cn(
                'inline-block px-3 py-1.5 rounded-md border font-medium text-xs shadow-sm',
                getComplianceStatusColor(
                  response.complianceStatus || 'non_compliant',
                ),
              )
            "
          >
            {{
              getComplianceStatusLabel(
                response.complianceStatus || "non_compliant",
              )
            }}
          </div>
        </div>

        <!-- Requirement Title -->
        <h1
          class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100"
        >
          {{ response.requirementTitle || "Requirement Details" }}
        </h1>
      </div>

      <!-- Compact Workflow Actions -->
      <div class="flex items-center gap-2">
        <div class="flex flex-col items-end gap-1 mr-2">
          <span
            class="text-[9px] font-bold uppercase tracking-widest text-muted-foreground mr-1"
            >RESPONSE STATUS</span
          >
          <div
            :class="
              cn(
                'h-6 px-3 text-[10px] font-bold border rounded-md inline-flex items-center uppercase tracking-tight',
                currentStatusInfo.color,
              )
            "
          >
            <component :is="currentStatusInfo.icon" class="w-3 h-3 mr-1.5" />
            {{ currentStatusInfo.label }}
          </div>
        </div>

        <div class="h-10 w-px bg-border mx-1"></div>

        <div class="flex items-center gap-1.5 pl-1">
          <!-- Primary Actions -->
          <Button
            v-if="canSubmit"
            size="sm"
            class="h-9 px-4 font-bold text-[11px] uppercase tracking-wider"
            @click="showSubmitDialog = true"
            :disabled="workflowLoading"
          >
            <Send class="w-3.5 h-3.5 mr-2" />
            Submit for Review
          </Button>

          <Button
            v-if="canApprove"
            size="sm"
            variant="outline"
            class="h-9 px-4 font-bold text-[11px] uppercase tracking-wider border-green-600 text-green-600 hover:bg-green-50 dark:hover:bg-green-950/20"
            @click="handleApprove"
            :disabled="workflowLoading"
          >
            <CheckCircle2 class="w-3.5 h-3.5 mr-2" />
            Approve
          </Button>

          <!-- More Actions Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9 border">
                <MoreVertical class="w-4 h-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56">
              <DropdownMenuLabel
                class="text-[10px] uppercase font-bold text-muted-foreground"
                >Response Actions</DropdownMenuLabel
              >
              <DropdownMenuSeparator />

              <DropdownMenuItem
                v-if="canRequestChanges"
                @click="showRejectDialog = true"
                class="py-2 cursor-pointer font-medium"
              >
                <RotateCcw class="mr-2 h-4 w-4 text-orange-600" />
                Request Changes
              </DropdownMenuItem>

              <DropdownMenuItem
                v-if="canRevertToActive"
                @click="showRevertDialog = true"
                class="py-2 cursor-pointer font-medium text-destructive"
              >
                <AlertTriangle class="mr-2 h-4 w-4" />
                Revert to Active (Admin Only)
              </DropdownMenuItem>

              <DropdownMenuItem
                class="py-2 cursor-pointer font-medium"
                @click="showHistoryDialog = true"
              >
                <History class="mr-2 h-4 w-4" />
                View History
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </div>

    <!-- Workflow Dialogs -->
    <Dialog v-model:open="showSubmitDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Submit for Review</DialogTitle>
          <DialogDescription>
            Are you sure you want to submit this response for review? It will be
            locked while being reviewed.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-[11px] font-bold uppercase"
              >Submission Note (Optional)</Label
            >
            <Textarea
              v-model="transitionNote"
              placeholder="Add details about your submission..."
              :rows="4"
              class="text-sm"
            />
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="showSubmitDialog = false"
            :disabled="workflowLoading"
            >Cancel</Button
          >
          <Button @click="handleSubmit" :disabled="workflowLoading">
            <Loader2 v-if="workflowLoading" class="w-4 h-4 animate-spin mr-2" />
            Submit for Review
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showRevertDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Revert to Active</DialogTitle>
          <DialogDescription>
            This will return the reviewed response to active status for
            corrections. This action can only be performed by Organization
            Admins.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-[11px] font-bold uppercase text-destructive"
              >Reason for Reversion *</Label
            >
            <Textarea
              v-model="transitionNote"
              placeholder="Explain why this response needs to be corrected..."
              :rows="4"
              class="text-sm border-destructive/30"
            />
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="showRevertDialog = false"
            :disabled="workflowLoading"
            >Cancel</Button
          >
          <Button
            variant="destructive"
            @click="handleRevertToActive"
            :disabled="workflowLoading || !transitionNote"
          >
            <Loader2 v-if="workflowLoading" class="w-4 h-4 animate-spin mr-2" />
            Revert to Active
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showRejectDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Request Changes</DialogTitle>
          <DialogDescription>
            This will return the response to active status for clarification or
            updates.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-[11px] font-bold uppercase text-destructive"
              >Reason for Request *</Label
            >
            <Textarea
              v-model="transitionNote"
              placeholder="Explain what needs to be improved or clarified..."
              :rows="4"
              class="text-sm border-destructive/30"
            />
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="showRejectDialog = false"
            :disabled="workflowLoading"
            >Cancel</Button
          >
          <Button
            variant="destructive"
            @click="handleRequestChanges"
            :disabled="workflowLoading || !transitionNote"
          >
            <Loader2 v-if="workflowLoading" class="w-4 h-4 animate-spin mr-2" />
            Request Changes
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- History Dialog -->
    <Dialog v-model:open="showHistoryDialog">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Response History</DialogTitle>
          <DialogDescription>
            Status change history for this response.
          </DialogDescription>
        </DialogHeader>
        <div class="py-4">
          <!-- Loading State -->
          <div
            v-if="historyLoading"
            class="flex items-center justify-center py-8"
          >
            <Loader2 class="w-6 h-6 animate-spin text-muted-foreground" />
          </div>

          <!-- Error State -->
          <div
            v-else-if="historyError"
            class="text-center py-8 text-destructive"
          >
            <p class="text-sm">{{ historyError }}</p>
            <Button
              variant="outline"
              size="sm"
              class="mt-2"
              @click="fetchHistory"
            >
              Retry
            </Button>
          </div>

          <!-- History List -->
          <div v-else-if="historyItems.length > 0" class="space-y-3">
            <div
              v-for="item in historyItems"
              :key="item.id"
              class="flex items-start gap-3 pb-3 border-b last:border-0"
            >
              <div class="mt-0.5">
                <div class="w-2 h-2 rounded-full bg-primary" />
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <p class="text-sm font-medium">
                    <span v-if="item.fromStatus" class="text-muted-foreground">
                      {{ item.fromStatus.replace(/_/g, " ") }} →
                    </span>
                    {{ item.toStatus.replace(/_/g, " ") }}
                  </p>
                </div>
                <p class="text-xs text-muted-foreground">
                  {{ new Date(item.createdAt).toLocaleString() }}
                </p>
                <div
                  v-if="item.user"
                  class="flex items-center gap-1 mt-1 text-xs text-muted-foreground"
                >
                  <span>by {{ item.user.name }}</span>
                </div>
                <p
                  v-if="item.note"
                  class="text-xs mt-2 text-foreground/80 bg-muted/50 p-2 rounded"
                >
                  {{ item.note }}
                </p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-8 text-muted-foreground">
            <History class="w-8 h-8 mx-auto mb-2 opacity-50" />
            <p class="text-sm">No history available</p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showHistoryDialog = false">
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Collapsible Guidance section -->
    <Accordion
      type="multiple"
      class="w-full border rounded-xl overflow-hidden bg-muted/5"
    >
      <!-- Requirement Description -->
      <AccordionItem
        value="description"
        v-if="response.requirementDescription"
        class="border-b-0"
      >
        <AccordionTrigger
          class="px-4 py-3 hover:bg-muted/30 transition-colors hover:no-underline"
        >
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 bg-blue-100 dark:bg-blue-900/50 rounded-md">
              <Info class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
            </div>
            <span
              class="text-xs font-bold uppercase tracking-wider text-muted-foreground"
            >
              Requirement Description
            </span>
          </div>
        </AccordionTrigger>
        <AccordionContent class="px-4 pb-4 pt-1 border-t bg-background/50">
          <div
            class="prose prose-sm dark:prose-invert max-w-none text-muted-foreground leading-relaxed"
            v-html="sanitize(response.requirementDescription)"
          />
        </AccordionContent>
      </AccordionItem>

      <!-- Evidence Hint -->
      <AccordionItem
        value="hint"
        v-if="response.requirementEvidenceHint"
        class="border-b-0"
      >
        <AccordionTrigger
          class="px-4 py-3 hover:bg-muted/30 transition-colors hover:no-underline border-t"
        >
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 bg-amber-100 dark:bg-amber-900/50 rounded-md">
              <Lightbulb
                class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"
              />
            </div>
            <span
              class="text-xs font-bold uppercase tracking-wider text-muted-foreground"
            >
              Evidence Guidance & Hints
            </span>
          </div>
        </AccordionTrigger>
        <AccordionContent
          class="px-4 pb-4 pt-4 border-t bg-amber-50/30 dark:bg-amber-950/10"
        >
          <div
            class="text-[13px] text-slate-700 dark:text-slate-300 prose prose-sm dark:prose-invert max-w-none"
            v-html="sanitize(response.requirementEvidenceHint)"
          />
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  </div>
</template>
