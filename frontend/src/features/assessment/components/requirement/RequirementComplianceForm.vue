<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { useAssessmentStore } from "../../stores/assessmentStore";
import type {
  AssessmentTreeNode,
  AssessmentResponse,
} from "../../types/assessment.types";
import type { AssessmentResponseStatus } from "../../types/assessment-response.types";
import {
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  Ban,
  ShieldCheck,
} from "lucide-vue-next";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardDescription,
} from "@/components/ui/card";

import { Label } from "@/components/ui/label";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import TiptapEditor from "@/components/ui/tiptap/TiptapEditor.vue";
import { cn } from "@/lib/utils";
import { ScrollArea } from "@/components/ui/scroll-area";

const props = defineProps<{
  response?: AssessmentResponse | null;
  requirement: AssessmentTreeNode;
}>();

const emit = defineEmits<{
  saved: [];
}>();

const store = useAssessmentStore();

const form = ref<{
  complianceStatus:
    | "non_compliant"
    | "partially_compliant"
    | "fully_compliant"
    | "not_applicable";
  comments: string;
}>({
  complianceStatus: "non_compliant",
  comments: "",
});

// Initialize form from existing response
watch(
  () => props.response,
  (newResponse) => {
    if (newResponse) {
      form.value = {
        complianceStatus:
          (newResponse.complianceStatus as
            | "non_compliant"
            | "partially_compliant"
            | "fully_compliant"
            | "not_applicable") || "non_compliant",
        comments: newResponse.comments || "",
      };
    }
  },
  { immediate: true },
);

// Response status - determines workflow state
const responseStatus = computed<AssessmentResponseStatus>(() => {
  return props.response?.status || "active";
});

// Check if form can be edited (only when status is "active")
const canEdit = computed(() => {
  return responseStatus.value === "active";
});

const complianceStatusOptions = [
  {
    value: "non_compliant",
    label: "Non-Compliant",
    fullLabel: "Non-Compliant",
    description:
      "The requirement is not met. Significant gaps exist that need immediate attention.",
    color: "text-red-600 dark:text-red-400",
    inactiveColor: "text-red-700/60 dark:text-red-300/60",
    bgColor: "bg-red-50 dark:bg-red-950/30",
    activeBg: "bg-red-100 dark:bg-red-900/50",
    borderColor: "border-red-200 dark:border-red-800/50",
    activeBorder: "border-red-500",
    icon: AlertCircle,
  },
  {
    value: "partially_compliant",
    label: "Partially",
    fullLabel: "Partially Compliant",
    description:
      "The requirement is partially met. Some aspects are implemented but improvements are needed.",
    color: "text-amber-600 dark:text-amber-400",
    inactiveColor: "text-amber-700/60 dark:text-amber-300/60",
    bgColor: "bg-amber-50 dark:bg-amber-950/30",
    activeBg: "bg-amber-100 dark:bg-amber-900/50",
    borderColor: "border-amber-200 dark:border-amber-800/50",
    activeBorder: "border-amber-500",
    icon: AlertTriangle,
  },
  {
    value: "fully_compliant",
    label: "Fully",
    fullLabel: "Fully Compliant",
    description:
      "The requirement is fully met. All criteria are satisfied with appropriate evidence.",
    color: "text-green-600 dark:text-green-400",
    inactiveColor: "text-green-700/60 dark:text-green-300/60",
    bgColor: "bg-green-50 dark:bg-green-950/30",
    activeBg: "bg-green-100 dark:bg-green-900/50",
    borderColor: "border-green-200 dark:border-green-800/50",
    activeBorder: "border-green-500",
    icon: CheckCircle2,
  },
  {
    value: "not_applicable",
    label: "N/A",
    fullLabel: "Not Applicable",
    description:
      "The requirement does not apply to this organization or context.",
    color: "text-slate-600 dark:text-slate-400",
    inactiveColor: "text-slate-700/60 dark:text-slate-300/60",
    bgColor: "bg-slate-50 dark:bg-slate-900/30",
    activeBg: "bg-slate-100 dark:bg-slate-800/50",
    borderColor: "border-slate-200 dark:border-slate-700/50",
    activeBorder: "border-slate-500",
    icon: Ban,
  },
];

async function handleSubmit() {
  if (!props.response || !canEdit.value) return;

  await store.updateResponse(props.response.id, {
    comments: form.value.comments,
    compliance_status: form.value.complianceStatus,
  });
  emit("saved");
}

// Expose handleSubmit to parent component
defineExpose({ handleSubmit });
</script>

<template>
  <Card class="flex-1 flex flex-col">
    <CardHeader class="pb-4 border-b">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="p-2 bg-primary/10 rounded-lg">
            <ShieldCheck class="w-5 h-5 text-primary" />
          </div>
          <div>
            <CardTitle class="text-sm font-bold uppercase tracking-tight">
              Assessment Compliance
            </CardTitle>
            <CardDescription class="text-[10px]">
              Rate the requirement fulfillment
            </CardDescription>
          </div>
        </div>
      </div>
    </CardHeader>

    <CardContent class="flex-1 p-0 overflow-hidden">
      <ScrollArea class="h-full">
        <div class="p-6 space-y-6">
          <!-- Workflow Message for Non-Active Status -->
          <div
            v-if="!canEdit && response"
            class="mb-6 p-4 rounded-xl border flex items-start gap-3"
            :class="
              cn(
                responseStatus === 'pending_review'
                  ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800/50'
                  : 'bg-green-50 dark:bg-green-950/30 border-green-200 dark:border-green-800/50',
              )
            "
          >
            <ShieldCheck
              :class="
                cn(
                  'w-5 h-5 mt-0.5 flex-shrink-0',
                  responseStatus === 'pending_review'
                    ? 'text-amber-600 dark:text-amber-400'
                    : 'text-green-600 dark:text-green-400',
                )
              "
            />
            <div class="flex-1">
              <p
                :class="
                  cn(
                    'text-sm font-semibold',
                    responseStatus === 'pending_review'
                      ? 'text-amber-900 dark:text-amber-100'
                      : 'text-green-900 dark:text-green-100',
                  )
                "
              >
                {{
                  responseStatus === "pending_review"
                    ? "This response is under review"
                    : "This response has been reviewed"
                }}
              </p>
              <p
                :class="
                  cn(
                    'text-xs mt-1',
                    responseStatus === 'pending_review'
                      ? 'text-amber-700 dark:text-amber-300'
                      : 'text-green-700 dark:text-green-300',
                  )
                "
              >
                {{
                  responseStatus === "pending_review"
                    ? "A reviewer will assess your compliance rating and findings. You'll be notified if any changes are needed."
                    : "A reviewer has approved this compliance assessment."
                }}
              </p>
            </div>
          </div>

          <form v-if="canEdit" @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Compliance Status Grid -->
            <div class="space-y-3">
              <Label
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2"
              >
                Compliance Status
                <span class="text-destructive font-bold">*</span>
              </Label>

              <TooltipProvider>
                <div class="grid grid-cols-4 gap-2">
                  <Tooltip
                    v-for="option in complianceStatusOptions"
                    :key="option.value"
                  >
                    <TooltipTrigger as-child>
                      <div
                        @click="form.complianceStatus = option.value as any"
                        :class="
                          cn(
                            'relative cursor-pointer rounded-lg border-2 px-2 py-2 transition-all duration-200 group flex flex-col items-center justify-center gap-1 text-center',
                            form.complianceStatus === option.value
                              ? cn(
                                  option.activeBg,
                                  option.activeBorder,
                                  'ring-1 ring-primary/10',
                                )
                              : cn(
                                  'border-transparent hover:bg-muted/50',
                                  option.bgColor,
                                ),
                          )
                        "
                      >
                        <component
                          :is="option.icon"
                          :class="
                            cn(
                              'w-4 h-4 transition-transform duration-200 group-hover:scale-110',
                              form.complianceStatus === option.value
                                ? option.color
                                : option.inactiveColor,
                            )
                          "
                        />
                        <p
                          :class="
                            cn(
                              'text-[9px] font-bold uppercase tracking-tight leading-tight',
                              form.complianceStatus === option.value
                                ? 'text-foreground'
                                : 'text-foreground/70',
                            )
                          "
                        >
                          {{ option.fullLabel || option.label }}
                        </p>

                        <!-- Selection Indicator -->
                        <div
                          v-if="form.complianceStatus === option.value"
                          class="absolute -top-1 -right-1"
                        >
                          <div class="bg-primary rounded-full p-0.5">
                            <CheckCircle2
                              class="w-2 h-2 text-primary-foreground"
                            />
                          </div>
                        </div>
                      </div>
                    </TooltipTrigger>
                    <TooltipContent side="bottom" class="max-w-[200px]">
                      <p class="text-xs font-semibold">
                        {{ option.fullLabel }}
                      </p>
                      <p class="text-[10px] text-white mt-1 leading-relaxed">
                        {{ option.description }}
                      </p>
                    </TooltipContent>
                  </Tooltip>
                </div>
              </TooltipProvider>
            </div>

            <!-- Comments -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label
                  class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >
                  Assessment Findings & Notes
                </Label>
                <Badge variant="outline" class="text-[9px] uppercase font-bold"
                  >Required</Badge
                >
              </div>
              <div class="rounded-lg border shadow-sm overflow-hidden bg-card">
                <TiptapEditor
                  v-model="form.comments"
                  placeholder="Describe your findings, exceptions, and supporting evidence for this rating..."
                />
              </div>
            </div>
          </form>

          <!-- Read-only View -->
          <div v-else class="space-y-6">
            <div class="space-y-3">
              <Label
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >Selected Compliance Status</Label
              >
              <TooltipProvider>
                <div class="flex flex-wrap gap-2">
                  <Tooltip
                    v-for="option in complianceStatusOptions"
                    :key="option.value"
                  >
                    <TooltipTrigger as-child>
                      <div
                        :class="
                          cn(
                            'px-4 py-2.5 rounded-lg border flex items-center gap-3 transition-all',
                            response?.complianceStatus === option.value
                              ? cn(
                                  option.bgColor,
                                  option.borderColor,
                                  'opacity-100 ring-2 ring-primary/5 shadow-sm cursor-help hover:shadow-md',
                                )
                              : 'opacity-40 grayscale pointer-events-none',
                          )
                        "
                      >
                        <component
                          :is="option.icon"
                          :class="cn('w-4 h-4', option.color)"
                        />
                        <span
                          class="text-xs font-bold uppercase tracking-tight"
                          >{{ option.fullLabel || option.label }}</span
                        >
                      </div>
                    </TooltipTrigger>
                    <TooltipContent
                      v-if="response?.complianceStatus === option.value"
                      side="bottom"
                      :delayDuration="200"
                      class="max-w-[220px]"
                    >
                      <div class="space-y-1.5">
                        <p class="text-xs font-semibold">
                          {{ option.fullLabel }}
                        </p>
                        <p class="text-[10px] text-white leading-relaxed">
                          {{ option.description }}
                        </p>
                      </div>
                    </TooltipContent>
                  </Tooltip>
                </div>
              </TooltipProvider>
            </div>

            <div class="space-y-2">
              <Label
                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                >Assessment Comments</Label
              >
              <div
                class="p-4 rounded-xl bg-muted/30 border border-dashed prose prose-sm max-w-none"
              >
                <div
                  v-if="response?.comments"
                  class="text-[13px] leading-relaxed text-foreground/90"
                  v-html="response.comments"
                />
                <p v-else class="text-xs italic text-muted-foreground">
                  No comments provided for this assessment.
                </p>
              </div>
            </div>
          </div>
        </div>
      </ScrollArea>
    </CardContent>
  </Card>
</template>
