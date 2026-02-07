<script setup lang="ts">
import { onMounted } from "vue";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { storeToRefs } from "pinia";
import AssessmentTreeNav from "./AssessmentTreeNav.vue";
import { Loader2 } from "lucide-vue-next";

defineProps<{
  assessmentId: string;
}>();

const emit = defineEmits<{
  nodeSelect: [nodeId: string];
}>();

const store = useAssessmentStore();
const { treeData, activeNodeId, treeLoading } = storeToRefs(store);

onMounted(() => {
  // Tree data is loaded by parent component via fetchAssessment
});

function handleSelect(nodeId: string) {
  store.setActiveNode(nodeId);
  emit("nodeSelect", nodeId);
}
</script>

<template>
  <div v-if="treeLoading" class="h-full flex items-center justify-center">
    <Loader2 class="w-6 h-6 animate-spin text-muted-foreground" />
  </div>

  <div v-else class="h-full flex flex-col">
    <AssessmentTreeNav
      :nodes="treeData"
      :selected-id="activeNodeId ?? undefined"
      :loading="treeLoading"
      @select="handleSelect"
    />
  </div>
</template>
