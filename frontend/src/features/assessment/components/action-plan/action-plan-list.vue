<script setup lang="ts">
import { Edit, Trash2, Calendar, User } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import type { ActionPlan } from "../../types/assessment.types";

interface Props {
  actionPlans: ActionPlan[];
  canEdit?: boolean;
}

withDefaults(defineProps<Props>(), {
  canEdit: true,
});

const emit = defineEmits<{
  edit: [actionPlan: ActionPlan];
  delete: [id: string];
}>();

function handleEdit(actionPlan: ActionPlan) {
  emit("edit", actionPlan);
}

function handleDelete(id: string) {
  if (confirm("Delete this action plan?")) {
    emit("delete", id);
  }
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}
</script>

<template>
  <div v-if="actionPlans.length > 0" class="space-y-2">
    <div
      v-for="plan in actionPlans"
      :key="plan.id"
      class="border rounded-lg p-2.5 bg-card/50 hover:bg-card transition-colors group"
    >
      <div class="flex items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
          <p
            class="font-bold text-[11px] truncate uppercase tracking-tight group-hover:text-primary transition-colors"
          >
            {{ plan.title }}
          </p>

          <div class="flex items-center gap-3 mt-1.5 overflow-hidden">
            <div class="flex items-center gap-1 shrink-0">
              <User class="h-2.5 w-2.5 text-muted-foreground/60" />
              <span
                class="text-[9px] text-muted-foreground truncate max-w-[80px]"
                >{{ plan.pic }}</span
              >
            </div>
            <div class="flex items-center gap-1 shrink-0">
              <Calendar class="h-2.5 w-2.5 text-primary/60" />
              <span class="text-[9px] text-primary font-bold">{{
                formatDate(plan.dueDate)
              }}</span>
            </div>
          </div>
        </div>

        <div
          v-if="canEdit"
          class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <Button
            type="button"
            variant="ghost"
            size="sm"
            class="h-6 w-6 p-0"
            @click="handleEdit(plan)"
          >
            <Edit class="h-3 w-3" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            class="h-6 w-6 p-0 hover:bg-destructive/10"
            @click="handleDelete(plan.id)"
          >
            <Trash2 class="h-3 w-3 text-destructive" />
          </Button>
        </div>
      </div>
    </div>
  </div>

  <p v-else class="text-[10px] text-muted-foreground italic px-1">
    No action plans assigned
  </p>
</template>
