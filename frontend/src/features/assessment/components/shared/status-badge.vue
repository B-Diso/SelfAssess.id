<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import type {
  AssessmentStatus,
  ActionPlanStatus,
} from "../../types/assessment.types";
import type { AssessmentResponseStatus } from "../../types/assessment-response.types";
import { computed } from "vue";

interface Props {
  status: AssessmentStatus | ActionPlanStatus | AssessmentResponseStatus;
  type?: "assessment" | "action-plan" | "response";
  size?: "default" | "sm";
}

const props = withDefaults(defineProps<Props>(), {
  type: "assessment",
  size: "default",
});

const variant = computed(() => {
  if (props.type === "assessment") {
    const status = props.status as AssessmentStatus;
    switch (status) {
      case "draft":
        return "secondary";
      case "active":
        return "default"; // Will be overridden with blue custom class
      case "pending_review":
        return "outline";
      case "reviewed":
        return "outline";
      case "pending_finish":
        return "outline";
      case "finished":
        return "default"; // Will be overridden with green custom class
      case "cancelled":
        return "destructive";
      default:
        return "secondary";
    }
  } else if (props.type === "response") {
    const status = props.status as AssessmentResponseStatus;
    switch (status) {
      case "active":
        return "default";
      case "pending_review":
        return "outline";
      case "reviewed":
        return "outline";
      default:
        return "secondary";
    }
  } else {
    const status = props.status as ActionPlanStatus;
    switch (status) {
      case "open":
        return "secondary";
      case "in_progress":
        return "default";
      case "closed":
        return "default";
      default:
        return "secondary";
    }
  }
});

const label = computed(() => {
  // Small size uses the same labels as default size
  // (removed the inconsistent short labels that were showing "Pending" instead of "Pending Review")
  
  // Full labels for default size
  if (props.type === "assessment") {
    const status = props.status as AssessmentStatus;
    switch (status) {
      case "draft":
        return "Draft";
      case "active":
        return "Active";
      case "pending_review":
        return "Pending Review";
      case "reviewed":
        return "Reviewed";
      case "pending_finish":
        return "Pending Finish";
      case "finished":
        return "Finished";
      case "cancelled":
        return "Cancelled";
      default:
        return status;
    }
  } else if (props.type === "response") {
    const status = props.status as AssessmentResponseStatus;
    switch (status) {
      case "active":
        return "Active";
      case "pending_review":
        return "Pending Review";
      case "reviewed":
        return "Reviewed";
      default:
        return status;
    }
  } else {
    const status = props.status as ActionPlanStatus;
    switch (status) {
      case "open":
        return "Open";
      case "in_progress":
        return "In Progress";
      case "closed":
        return "Completed";
      default:
        return status;
    }
  }
});

const badgeClass = computed(() => {
  const sizeClass = props.size === "sm"
    ? "text-[10px] px-1.5 py-0 h-5 whitespace-nowrap"
    : "";

  // Custom color overrides for assessment statuses
  if (props.type === "assessment") {
    const status = props.status as AssessmentStatus;
    switch (status) {
      case "active":
        // Blue badge for active assessments
        return `${sizeClass} bg-blue-500 text-white border-transparent hover:bg-blue-600`;
      case "finished":
        // Green badge for finished assessments
        return `${sizeClass} bg-green-500 text-white border-transparent hover:bg-green-600`;
      default:
        return sizeClass;
    }
  }

  return sizeClass;
});
</script>

<template>
  <Badge :variant="variant" :class="badgeClass">
    {{ label }}
  </Badge>
</template>
