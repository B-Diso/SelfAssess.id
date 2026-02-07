<script setup lang="ts">
import { computed } from "vue";
import {
  MoreHorizontal,
  Eye,
  Pencil,
  Trash2,
  Play,
  Send,
  CheckCircle,
  Flag,
  RotateCcw,
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import type { Assessment } from "@/features/assessment/types/assessment.types";
import { useUserStore } from "@/features/auth/stores/userStore";

interface Props {
  assessment: Assessment;
}

const props = defineProps<Props>();
const userStore = useUserStore();

const emit = defineEmits<{
  (e: "view", assessment: Assessment): void;
  (e: "edit", assessment: Assessment): void;
  (e: "editDetail", assessment: Assessment): void;
  (e: "delete", assessment: Assessment): void;
  (e: "activate", assessment: Assessment): void;
  (e: "cancel", assessment: Assessment): void;
  (e: "submit", assessment: Assessment): void;
  (e: "approve", assessment: Assessment): void;
  (e: "reject", assessment: Assessment): void;
  (e: "requestFinish", assessment: Assessment): void;
  (e: "finalize", assessment: Assessment): void;
  (e: "revert", assessment: Assessment): void;
}>();

type ActionType =
  | "view"
  | "activate"
  | "edit"
  | "editDetail"
  | "delete"
  | "cancel"
  | "submit"
  | "approve"
  | "reject"
  | "requestFinish"
  | "finalize"
  | "revert";

interface ActionItem {
  type: ActionType;
  label: string;
  icon: typeof Eye;
  variant?: "default" | "destructive";
}

const isSuperAdmin = computed(() => userStore.isSuperAdmin);
const isOrgAdmin = computed(() => userStore.isOrganizationAdmin);

const availableActions = computed<ActionItem[]>(() => {
  const actions: ActionItem[] = [];
  const status = props.assessment.status;

  switch (status) {
    case "draft":
      // Org Admin: Activate, Edit Detail, Delete
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          { type: "activate", label: "Activate", icon: Play },
          { type: "editDetail", label: "Edit", icon: Pencil },
          {
            type: "delete",
            label: "Delete",
            icon: Trash2,
            variant: "destructive",
          }
        );
      }
      break;

    case "active":
      // All: View Progress
      actions.push({ type: "view", label: "View Progress", icon: Eye });

      // Org Admin: Edit ONLY (basic edit is not allowed after activation)
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          { type: "editDetail", label: "Edit", icon: Pencil },
          {
            type: "delete",
            label: "Delete",
            icon: Trash2,
            variant: "destructive",
          }
        );
      }

      // Org User/Admin: Submit for Review
      actions.push({ type: "submit", label: "Submit for Review", icon: Send });

      break;

    case "pending_review":
      // All: View
      actions.push({ type: "view", label: "View", icon: Eye });

      // Org Admin: Edit ONLY
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push({ type: "editDetail", label: "Edit", icon: Pencil });
      }

      // Super Admin: Approve Review
      if (isSuperAdmin.value) {
        actions.push(
          { type: "approve", label: "Approve Review", icon: CheckCircle }
        );
      }

      // Org Admin: Reject (back to active)
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          {
            type: "reject",
            label: "Reject / Return",
            icon: RotateCcw,
            variant: "destructive"
          }
        );
      }
      break;

    case "reviewed":
      // All: View
      actions.push({ type: "view", label: "View", icon: Eye });

      // Org Admin: Edit, Request Finish
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          { type: "editDetail", label: "Edit", icon: Pencil },
          { type: "requestFinish", label: "Request Finish", icon: Flag }
        );
      }
      break;

    case "pending_finish":
      // All: View
      actions.push({ type: "view", label: "View", icon: Eye });

      // Org Admin: Edit
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push({ type: "editDetail", label: "Edit", icon: Pencil });
      }

      // Super Admin: Finalize
      if (isSuperAdmin.value) {
        actions.push(
          { type: "finalize", label: "Finalize", icon: CheckCircle }
        );
      }

      // Org Admin: Revert to Active
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          {
            type: "revert",
            label: "Revert to Active",
            icon: RotateCcw,
            variant: "destructive"
          }
        );
      }
      break;

    case "finished":
      // All: View only (no edit actions allowed)
      actions.push({ type: "view", label: "View", icon: Eye });
      break;

    case "cancelled":
      // Org Admin: Reactivate
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          { type: "activate", label: "Reactivate", icon: Play }
        );
      }
      // View only (no edit actions allowed)
      actions.push({ type: "view", label: "View", icon: Eye });
      break;

    case "rejected":
      // Org Admin: Reactivate
      if (isOrgAdmin.value || isSuperAdmin.value) {
        actions.push(
          { type: "activate", label: "Reactivate", icon: Play }
        );
      }
      // View only (no edit actions allowed)
      actions.push({ type: "view", label: "View", icon: Eye });
      break;

    default:
      actions.push({ type: "view", label: "View", icon: Eye });
  }

  return actions;
});

function handleAction(action: ActionItem) {
  switch (action.type) {
    case "view":
      emit("view", props.assessment);
      break;
    case "activate":
      emit("activate", props.assessment);
      break;
    case "edit":
      emit("edit", props.assessment);
      break;
    case "editDetail":
      emit("editDetail", props.assessment);
      break;
    case "delete":
      emit("delete", props.assessment);
      break;
    case "cancel":
      emit("cancel", props.assessment);
      break;
    case "submit":
      emit("submit", props.assessment);
      break;
    case "approve":
      emit("approve", props.assessment);
      break;
    case "reject":
      emit("reject", props.assessment);
      break;
    case "requestFinish":
      emit("requestFinish", props.assessment);
      break;
    case "finalize":
      emit("finalize", props.assessment);
      break;
    case "revert":
      emit("revert", props.assessment);
      break;
  }
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" class="h-8 w-8">
        <MoreHorizontal class="h-4 w-4" />
        <span class="sr-only">Open menu</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuItem
        v-for="action in availableActions"
        :key="action.type"
        @click="handleAction(action)"
        :class="action.variant === 'destructive' ? 'text-red-600 focus:text-red-600' : ''"
      >
        <component :is="action.icon" class="mr-2 h-4 w-4" />
        {{ action.label }}
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
