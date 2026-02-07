<script setup lang="ts">
import type { StandardRequirement } from "../../types/standard";

interface Props {
  requirement?: StandardRequirement | null;
}

defineProps<Props>();

// Note: Scoring logic fields (weight, isMandatory) have been removed from the schema
// This component is temporarily disabled until the scoring system is redesigned
</script>

<template>
  <div class="space-y-6">
    <div class="bg-muted/30 rounded-xl p-6 border">
      <div class="flex items-center gap-2 mb-4">
        <svg
          class="w-5 h-5 text-primary"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
          />
        </svg>
        <h3 class="text-sm font-semibold">Scoring Configuration</h3>
      </div>

      <div class="text-sm text-muted-foreground">
        <p>
          Scoring configuration is currently being redesigned to align with the
          new schema. This feature will be available in a future update.
        </p>
      </div>
    </div>
  </div>
</template>
