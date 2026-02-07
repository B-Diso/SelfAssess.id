<script setup lang="ts">
import { onMounted, computed, ref } from "vue";
import { useRoute } from "vue-router";
import { useAssessmentStore } from "../stores/assessmentStore";
import {
  ResizablePanel,
  ResizableHandle,
  ResizablePanelGroup,
} from "@/components/ui/resizable";
import SelfAssessmentTree from "../components/tree/SelfAssessmentTree.vue";
import RequirementDetail from "../components/requirement/RequirementDetail.vue";
import PrincipleDetailView from "../components/detail/PrincipleDetailView.vue";
import AssessmentWorkflowBar from "../components/workflow/AssessmentWorkflowBar.vue";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { storeToRefs } from "pinia";
import { Loader2, Save } from "lucide-vue-next";
import { Button } from "@/components/ui/button";

const route = useRoute();
const store = useAssessmentStore();
const {
  assessment,
  loading,
  activeNode,
  activeResponse,
  isEditable,
  saving,
  canSubmit,
  canRequestChanges,
  canFinalize,
} = storeToRefs(store);

// Ref to RequirementDetail component
const requirementDetailRef = ref<InstanceType<typeof RequirementDetail> | null>(null);

// Check if we can save the current response
const canSaveResponse = computed(() => {
  return activeResponse.value && isEditable.value;
});

// Save current response
async function handleSaveResponse() {
  // Call saveForm on RequirementDetail which will trigger form submission
  await requirementDetailRef.value?.saveForm();
}

onMounted(() => {
  const id = route.params.id as string;
  if (id) {
    store.fetchAssessment(id);
  }
});

function handleNodeSelect() {
  // Node selection is handled by the store
}
</script>

<template>
  <div class="flex flex-col h-full w-full bg-background">
    <!-- Main Content with Resizable Panels -->
    <div class="flex-1 flex overflow-hidden">
      <ResizablePanelGroup
        direction="horizontal"
        class="h-full w-full rounded-lg border"
      >
        <!-- Left Panel: Tree Navigation -->
        <ResizablePanel
          :default-size="25"
          :min-size="20"
          :max-size="40"
          class="min-w-[280px] max-w-[500px]"
        >
          <!-- Initial loading or no assessment data -->
          <div
            v-if="loading && !assessment"
            class="flex items-center justify-center h-full"
          >
            <div class="text-center space-y-3">
              <Loader2 class="w-8 h-8 animate-spin mx-auto text-primary" />
              <p class="text-sm text-muted-foreground">Loading assessment...</p>
            </div>
          </div>

          <SelfAssessmentTree
            v-else-if="assessment"
            :assessment-id="assessment.id"
            @node-select="handleNodeSelect"
          />

          <div v-else class="flex items-center justify-center h-full p-8">
            <p class="text-sm text-muted-foreground text-center">
              Assessment not found
            </p>
          </div>
        </ResizablePanel>

        <!-- Resizable Handle -->
        <ResizableHandle
          with-handle
          class="w-1 bg-border hover:bg-primary/50 transition-colors"
        />

        <!-- Right Panel: Detail Canvas -->
        <ResizablePanel :default-size="75" class="min-w-[400px]">
          <div
            v-if="loading && !assessment"
            class="flex items-center justify-center h-full"
          >
            <div class="text-center space-y-3">
              <Loader2 class="w-8 h-8 animate-spin mx-auto text-primary" />
              <p class="text-sm text-muted-foreground">Loading details...</p>
            </div>
          </div>

          <ScrollArea
            v-else-if="
              assessment && activeNode && activeNode.type === 'requirement'
            "
            class="h-full"
          >
            <RequirementDetail ref="requirementDetailRef" :assessment-id="assessment.id" />
          </ScrollArea>

          <ScrollArea
            v-else-if="
              assessment &&
              activeNode &&
              (activeNode.type === 'domain' ||
                activeNode.type === 'element' ||
                activeNode.type === 'section')
            "
            class="h-full"
          >
            <PrincipleDetailView :node="activeNode" />
          </ScrollArea>

          <div v-else class="flex items-center justify-center h-full p-8">
            <p class="text-sm text-muted-foreground text-center">
              Select a requirement or section to view details
            </p>
          </div>
        </ResizablePanel>
      </ResizablePanelGroup>
    </div>

    <!-- Sticky Footer: Save Response Action -->
    <TooltipProvider>
      <div
        v-if="assessment && activeResponse"
        class="border-t bg-muted/40 backdrop-blur-md shrink-0 px-4 py-2 shadow-sm z-10"
      >
        <div class="max-w-7xl mx-auto flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Tooltip>
              <TooltipTrigger as-child>
                <span
                  class="text-[10px] font-medium uppercase tracking-wide text-muted-foreground cursor-help hover:text-foreground transition-colors"
                >
                  {{ activeNode?.code || "Response" }}
                </span>
              </TooltipTrigger>
              <TooltipContent side="top" :delayDuration="200">
                <p class="text-xs">
                  {{ activeNode?.title || "Assessment Response" }}
                </p>
              </TooltipContent>
            </Tooltip>

            <Tooltip v-if="saving">
              <TooltipTrigger as-child>
                <span
                  class="text-[10px] text-muted-foreground italic flex items-center gap-1.5"
                >
                  <span
                    class="inline-flex h-1.5 w-1.5 rounded-full bg-yellow-500 animate-pulse"
                  ></span>
                  Saving...
                </span>
              </TooltipTrigger>
              <TooltipContent side="top" :delayDuration="200">
                <p class="text-xs">Changes are being saved to the server</p>
              </TooltipContent>
            </Tooltip>
          </div>

          <Tooltip>
            <TooltipTrigger as-child>
              <div class="inline-block">
                <Button
                  v-if="canSaveResponse"
                  size="sm"
                  variant="default"
                  class="h-8 px-4 font-semibold text-[11px] whitespace-nowrap"
                  :disabled="saving"
                  @click="handleSaveResponse"
                >
                  <Loader2
                    v-if="saving"
                    class="w-3.5 h-3.5 animate-spin mr-1.5"
                  />
                  <Save v-else class="w-3.5 h-3.5 mr-1.5" />
                  Save Response
                </Button>
              </div>
            </TooltipTrigger>
            <TooltipContent v-if="!saving" side="top" :delayDuration="200">
              <p class="text-xs">Save the current assessment response</p>
            </TooltipContent>
          </Tooltip>
        </div>
      </div>
    </TooltipProvider>

    <!-- Workflow Actions Footer -->
    <div
      v-if="assessment"
      class="border-t bg-muted/40 backdrop-blur-md shrink-0 px-6 py-3 shadow-sm z-10"
    >
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          <span v-if="activeNode" class="font-medium">
            {{ activeNode.code || "Response" }}
          </span>
          <span v-else>Select a requirement to begin</span>
        </div>
        <AssessmentWorkflowBar
          v-if="assessment"
          :assessment="assessment"
          :can-submit="canSubmit"
          :can-request-changes="canRequestChanges"
          :can-finalize="canFinalize"
        />
      </div>
    </div>
  </div>
</template>
