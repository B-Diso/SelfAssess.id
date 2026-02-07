<script setup lang="ts">
import { Pencil, Trash2, Calendar, User, AlignLeft } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import type { ActionPlan } from "../../types/assessment.types";
import { computed } from "vue";

interface Props {
  actionPlan: ActionPlan;
  canEdit?: boolean;
  canDelete?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  canEdit: true,
  canDelete: true,
});

const emit = defineEmits<{
  edit: [actionPlan: ActionPlan];
  delete: [id: string];
}>();

function handleEdit() {
  emit("edit", props.actionPlan);
}

function handleDelete() {
  if (confirm("Delete this action plan?")) {
    emit("delete", props.actionPlan.id);
  }
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

const isOverdue = computed(() => {
  if (!props.actionPlan.dueDate) return false;
  const dueDate = new Date(props.actionPlan.dueDate);
  dueDate.setHours(0, 0, 0, 0);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return dueDate < today;
});
</script>

<template>
  <Card
    :class="
      cn('group transition-all hover:border-primary/50', {
        'border-destructive/50 bg-destructive/5': isOverdue,
      })
    "
  >
    <CardContent class="p-3">
      <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0 space-y-2">
          <div class="flex items-center gap-2">
            <h4
              class="text-sm font-bold truncate group-hover:text-primary transition-colors"
            >
              {{ actionPlan.title }}
            </h4>
            <span
              v-if="isOverdue"
              class="text-[10px] bg-destructive text-destructive-foreground px-1.5 py-0.5 rounded font-bold uppercase tracking-wider"
              >Overdue</span
            >
          </div>

          <p
            v-if="actionPlan.actionPlan"
            class="text-[11px] text-muted-foreground leading-relaxed flex items-start gap-1.5"
          >
            <AlignLeft class="w-3 h-3 mt-0.5 shrink-0 opacity-50" />
            <span>{{ actionPlan.actionPlan }}</span>
          </p>

          <div class="flex items-center gap-4 pt-1">
            <div
              class="flex items-center gap-1.5 text-[10px] text-muted-foreground"
            >
              <User class="h-3 w-3 opacity-70" />
              <span class="font-medium">{{ actionPlan.pic }}</span>
            </div>
            <div
              class="flex items-center gap-1.5 text-[10px]"
              :class="
                isOverdue
                  ? 'text-destructive font-bold'
                  : 'text-primary font-bold'
              "
            >
              <Calendar class="h-3 w-3" />
              <span>Due: {{ formatDate(actionPlan.dueDate) }}</span>
            </div>
          </div>
        </div>

        <div
          class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <Button
            v-if="canEdit"
            type="button"
            variant="ghost"
            size="icon"
            class="h-7 w-7"
            @click="handleEdit"
          >
            <Pencil class="h-3.5 w-3.5" />
          </Button>

          <Button
            v-if="canDelete"
            type="button"
            variant="ghost"
            size="icon"
            class="h-7 w-7 text-destructive hover:text-destructive hover:bg-destructive/10"
            @click="handleDelete"
          >
            <Trash2 class="h-3.5 w-3.5" />
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
