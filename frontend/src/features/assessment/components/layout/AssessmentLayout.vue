<script setup lang="ts">
import AssessmentSidebar from "./AssessmentSidebar.vue";
import AssessmentHeader from "./AssessmentHeader.vue";
</script>

<template>
  <div class="flex h-full w-full overflow-hidden bg-background">
    <AssessmentSidebar class="w-64 border-r flex-shrink-0 h-full" />
    <div class="flex flex-1 flex-col overflow-hidden min-h-0">
      <slot name="header">
        <AssessmentHeader class="border-b flex-shrink-0" />
      </slot>
      <main class="flex-1 overflow-hidden bg-muted/10">
        <slot />
      </main>
    </div>
  </div>
</template>
