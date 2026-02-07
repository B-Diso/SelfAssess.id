<script setup lang="ts">
import { useAssessmentStore } from "../../stores/assessmentStore";
import { Button } from "@/components/ui/button";
import StatusBadge from "../shared/status-badge.vue";
import { ChevronRight, Save } from "lucide-vue-next";

const store = useAssessmentStore();
</script>

<template>
  <div class="flex items-center justify-between px-6 py-3 bg-card">
    <div class="flex items-center space-x-2 text-sm text-muted-foreground">
      <span>Assessments</span>
      <ChevronRight class="w-4 h-4" />
      <span class="font-medium text-foreground">{{
        store.assessment?.name
      }}</span>
    </div>

    <div class="flex items-center space-x-4">
      <StatusBadge v-if="store.assessment" :status="store.assessment.status" />
      <Button size="sm" variant="outline">
        <Save class="w-4 h-4 mr-2" />
        Save
      </Button>
      <Button size="sm">Submit</Button>
    </div>
  </div>
</template>
