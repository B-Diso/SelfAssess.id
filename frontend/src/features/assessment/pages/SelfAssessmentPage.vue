<script setup lang="ts">
import { onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAssessmentStore } from "../stores/assessmentStore";
import { storeToRefs } from "pinia";
import AssessmentLayout from "../components/layout/AssessmentLayout.vue";
import SelfAssessmentTree from "../components/tree/SelfAssessmentTree.vue";
import RequirementComplianceForm from "../components/requirement/RequirementComplianceForm.vue";
import PrincipleDetailView from "../components/detail/PrincipleDetailView.vue";
import AttachmentManagementCard from "../components/attachment/AttachmentManagementCard.vue";
import ActionPlanPanel from "../components/panels/ActionPlanPanel.vue";
import NotesPanel from "../components/panels/NotesPanel.vue";
import AssessmentWorkflowBar from "../components/workflow/AssessmentWorkflowBar.vue";
import {
  ResizablePanelGroup,
  ResizablePanel,
  ResizableHandle,
} from "@/components/ui/resizable";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { Loader2 } from "lucide-vue-next";

const route = useRoute();
const store = useAssessmentStore();
const {
  activeNode,
  activeResponse,
  loading,
  assessment,
  canSubmit,
  canRequestChanges,
  canFinalize,
} = storeToRefs(store);

onMounted(() => {
  const id = route.params.id as string;
  if (id) {
    store.fetchAssessment(id);
  }
});

function handleNodeSelect(_nodeId: string) {
  // Node selection handled by store
}

const isRequirement = () => activeNode.value?.type === "requirement";
const isPrincipleOrElement = () =>
  activeNode.value?.type === "element" ||
  activeNode.value?.type === "domain" ||
  activeNode.value?.type === "section";
</script>

<template>
  <AssessmentLayout>
    <ResizablePanelGroup direction="horizontal">
      <!-- Left Panel: Tree Navigation -->
      <ResizablePanel :min-size="20" :default-size="28" :max-size="40">
        <SelfAssessmentTree
          :assessment-id="route.params.id as string"
          @node-select="handleNodeSelect"
        />
      </ResizablePanel>

      <ResizableHandle />

      <!-- Right Panel: Main Content -->
      <ResizablePanel :default-size="72">
        <div v-if="loading" class="h-full flex items-center justify-center">
          <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
        </div>

        <div
          v-else-if="!assessment"
          class="h-full flex items-center justify-center"
        >
          <Card class="max-w-md w-full">
            <CardHeader>
              <CardTitle>Assessment Not Found</CardTitle>
              <CardDescription>
                The requested assessment could not be loaded.
              </CardDescription>
            </CardHeader>
          </Card>
        </div>

        <div
          v-else-if="!activeNode"
          class="h-full flex flex-col overflow-hidden"
        >
          <!-- Empty State -->
          <div class="flex-1 flex items-center justify-center p-4">
            <Card class="max-w-lg w-full">
              <CardHeader class="text-center">
                <CardTitle class="text-2xl">Self Assessment</CardTitle>
                <CardDescription>
                  Select a requirement from the tree navigation to begin your
                  assessment.
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="text-center text-sm text-muted-foreground">
                  <p class="font-medium">{{ assessment.name }}</p>
                  <p class="mt-1">{{ assessment.standardId }}</p>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                  <div>
                    <p class="text-2xl font-bold">
                      {{ store.progressPercentage }}%
                    </p>
                    <p class="text-xs text-muted-foreground">Complete</p>
                  </div>
                  <div>
                    <p class="text-2xl font-bold">
                      {{ store.summary?.completedRequirements || 0 }}
                    </p>
                    <p class="text-xs text-muted-foreground">Completed</p>
                  </div>
                  <div>
                    <p class="text-2xl font-bold">
                      {{ store.summary?.totalRequirements || 0 }}
                    </p>
                    <p class="text-xs text-muted-foreground">Total</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Workflow Bar -->
          <AssessmentWorkflowBar
            :assessment="assessment"
            :can-submit="canSubmit"
            :can-request-changes="canRequestChanges"
            :can-finalize="canFinalize"
          />
        </div>

        <!-- Requirement View -->
        <div
          v-else-if="isRequirement()"
          class="h-full flex flex-col overflow-hidden"
        >
          <Tabs v-model="store.activeTab" class="h-full flex flex-col">
            <!-- Tab list header (sticky, not scrollable) -->
            <div class="border-b bg-background px-4 pt-2 flex-shrink-0">
              <TabsList class="h-10">
                <TabsTrigger value="response" class="px-4"
                  >Response</TabsTrigger
                >
                <TabsTrigger value="evidence" class="px-4"
                  >Attachments</TabsTrigger
                >
                <TabsTrigger value="action-plans" class="px-4"
                  >Action Plans</TabsTrigger
                >
                <TabsTrigger value="notes" class="px-4">Notes</TabsTrigger>
              </TabsList>
            </div>

            <!-- Tab content area (scrollable) -->
            <div class="flex-1 overflow-hidden">
              <TabsContent value="response" class="h-full m-0">
                <ScrollArea class="h-full w-full">
                  <div class="p-4">
                    <RequirementComplianceForm
                      :response="activeResponse"
                      :requirement="activeNode"
                    />
                  </div>
                </ScrollArea>
              </TabsContent>

              <TabsContent value="evidence" class="h-full m-0">
                <ScrollArea class="h-full w-full">
                  <div class="p-4">
                    <AttachmentManagementCard
                      v-if="activeResponse"
                      :response-id="activeResponse.id"
                      :assessment-id="assessment?.id ?? ''"
                      :is-editable="true"
                    />
                  </div>
                </ScrollArea>
              </TabsContent>

              <TabsContent value="action-plans" class="h-full m-0">
                <ScrollArea class="h-full w-full">
                  <div class="p-4">
                    <ActionPlanPanel
                      v-if="activeResponse"
                      :response-id="activeResponse.id"
                      :assessment-id="assessment?.id ?? ''"
                    />
                  </div>
                </ScrollArea>
              </TabsContent>

              <TabsContent value="notes" class="h-full m-0">
                <ScrollArea class="h-full w-full">
                  <div class="p-4">
                    <NotesPanel
                      v-if="activeResponse"
                      :response="activeResponse"
                    />
                  </div>
                </ScrollArea>
              </TabsContent>
            </div>
          </Tabs>

          <!-- Workflow Bar -->
          <AssessmentWorkflowBar
            :assessment="assessment"
            :can-submit="canSubmit"
            :can-request-changes="canRequestChanges"
            :can-finalize="canFinalize"
          />
        </div>

        <!-- Principle/Element/Section View (Read-Only) -->
        <div
          v-else-if="isPrincipleOrElement()"
          class="h-full flex flex-col overflow-hidden"
        >
          <ScrollArea class="flex-1 w-full">
            <PrincipleDetailView :node="activeNode" />
          </ScrollArea>

          <!-- Workflow Bar -->
          <AssessmentWorkflowBar
            :assessment="assessment"
            :can-submit="canSubmit"
            :can-request-changes="canRequestChanges"
            :can-finalize="canFinalize"
          />
        </div>
      </ResizablePanel>
    </ResizablePanelGroup>
  </AssessmentLayout>
</template>
