<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ResizablePanel,
  ResizableHandle,
  ResizablePanelGroup,
} from "@/components/ui/resizable";
import { toast } from "vue-sonner";

import TreeNav from "../../components/management/TreeNav.vue";
import DetailPanel from "../../components/management/DetailPanel.vue";
import StandardSectionDialog from "../../components/modal/StandardSectionDialog.vue";
import StandardRequirementDialog from "../../components/modal/StandardRequirementDialog.vue";
import DeleteSectionDialog from "../../components/modal/DeleteSectionDialog.vue";
import DeleteRequirementDialog from "../../components/modal/DeleteRequirementDialog.vue";
import { useStandardStore } from "../../stores/standardStore";
import { getApiErrorMessage } from "@/lib/api-error";
import type {
  Standard,
  StandardSection,
  StandardRequirement,
} from "../../types/standard";
import { useDeleteRequirement } from "../../composables/useRequirements";
import { useDeleteDomain } from "../../composables/useDomains";

const route = useRoute();
const router = useRouter();
const store = useStandardStore();

const standardId = computed(() => route.params.id as string);
const isCreating = computed(() => route.name === "standards.create");

// UI State
const viewMode = ref<"standard" | "section" | "requirement">("standard");
const selectedSectionId = ref<string | null>(null);
const selectedItem = ref<StandardSection | StandardRequirement | null>(null);

// Dialog State
const showSectionDialog = ref(false);
const showRequirementDialog = ref(false);
const showDeleteDialog = ref(false);
const showDeleteReqDialog = ref(false);
const isSubmitting = ref(false);
const dialogParentId = ref<string | null>(null);
const editingNode = ref<StandardSection | StandardRequirement | null>(null);

const deleteMutationSelection = useDeleteDomain();
const deleteMutationRequirement = useDeleteRequirement();

// Fetch data on mount
onMounted(async () => {
  if (!isCreating.value && standardId.value) {
    await loadStandardData();
  }
});

watch(showDeleteDialog, (open) => {
  if (!open) {
    selectedItem.value = null;
  }
});

watch(showDeleteReqDialog, (open) => {
  if (!open) {
    selectedItem.value = null;
  }
});

async function loadStandardData() {
  try {
    await Promise.all([
      store.fetchStandard(standardId.value),
      store.fetchSectionsTree(standardId.value),
    ]);
  } catch (error) {
    toast.error("Failed to load standard data");
    router.push("/standards");
  }
}

function handleSelect(node: StandardSection | StandardRequirement | null) {
  if (node === null) {
    selectedItem.value = null;
    selectedSectionId.value = null;
    store.setSelectedItemId(null);
    viewMode.value = "standard";
    return;
  }

  if ("displayCode" in node) {
    viewMode.value = "requirement";
    selectedItem.value = node;
    store.setSelectedItemId(node.id);
  } else {
    viewMode.value = "section";
    selectedItem.value = node as StandardSection;
    selectedSectionId.value = node.id;
    store.setSelectedItemId(node.id);
  }
}

function handleToggle(nodeId: string) {
  store.toggleExpandedNode(nodeId);
}

function handleAddChild(parent: StandardSection) {
  dialogParentId.value = parent.id;
  editingNode.value = null;
  showSectionDialog.value = true;
}

function handleAddRootSection() {
  dialogParentId.value = null;
  editingNode.value = null;
  showSectionDialog.value = true;
}

function handleAddRequirement(section: StandardSection) {
  selectedSectionId.value = section.id;
  editingNode.value = null;
  showRequirementDialog.value = true;
}

async function handleCreateSectionSubmit(data: any) {
  isSubmitting.value = true;
  try {
    await store.createSection(standardId.value, {
      ...data,
      standardId: standardId.value,
      parentId: dialogParentId.value,
    });
    toast.success("Section created successfully");
    showSectionDialog.value = false;
  } catch (error) {
    toast.error("Failed to create section");
  } finally {
    isSubmitting.value = false;
  }
}

async function handleCreateRequirementSubmit(data: any) {
  if (!selectedSectionId.value) return;
  isSubmitting.value = true;
  try {
    await store.createRequirement(standardId.value, {
      ...data,
      standardSectionId: selectedSectionId.value,
    });
    toast.success("Requirement created successfully");
    showRequirementDialog.value = false;
    // Reload tree to show new requirement
    await store.fetchSectionsTree(standardId.value);
  } catch (error) {
    toast.error("Failed to create requirement");
  } finally {
    isSubmitting.value = false;
  }
}

function handleDelete(node: StandardSection) {
  selectedItem.value = node;
  showDeleteDialog.value = true;
}

function handleDeleteReq(node: StandardRequirement) {
  selectedItem.value = node;
  showDeleteReqDialog.value = true;
}

async function handleDeleteSection() {
  if (!selectedItem.value) return;

  try {
    const response = await deleteMutationSelection.mutateAsync(
      selectedItem.value?.id,
    );
    toast.success(response.message ?? "Section deleted successfully");
    showDeleteDialog.value = false;
    selectedItem.value = null;
    viewMode.value = "standard";
     // Reload tree to show new requirement
     await store.fetchSectionsTree(standardId.value);
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to delete section"));
  }
}

async function handleDeleteRequirement() {
  if (!selectedItem.value) return;

  try {
    const response = await deleteMutationRequirement.mutateAsync(
      selectedItem.value?.id,
    );
    toast.success(response.message ?? "Requirement deleted successfully");
    showDeleteReqDialog.value = false;
    selectedItem.value = null;
    viewMode.value = "standard";
     // Reload tree to show new requirement
     await store.fetchSectionsTree(standardId.value);
  } catch (err) {
    toast.error(getApiErrorMessage(err, "Failed to delete requirement"));
  }
}

async function handleUpdateStandard(data: Partial<Standard>) {
  try {
    await store.updateStandard(standardId.value, data);
    toast.success("Standard updated successfully");
  } catch (error) {
    toast.error("Failed to update standard");
  }
}

async function handleUpdateSection(data: Partial<StandardSection>) {
  if (!selectedItem.value) return;

  try {
    await store.updateSection(selectedItem.value.id, data);
    toast.success("Section updated successfully");
  } catch (error) {
    toast.error("Failed to update section");
  }
}

async function handleUpdateRequirement(data: Partial<StandardRequirement>) {
  if (!selectedItem.value) return;

  try {
    await store.updateRequirement(selectedItem.value.id, data);
    toast.success("Requirement updated successfully");
  } catch (error) {
    toast.error("Failed to update requirement");
  }
}

async function handleDeleteStandard() {
  if (
    !confirm(
      "Are you sure you want to delete this standard? This action cannot be undone.",
    )
  ) {
    return;
  }

  try {
    await store.deleteStandard(standardId.value);
    toast.success("Standard deleted successfully");
    router.push("/standards");
  } catch (error) {
    toast.error("Failed to delete standard");
  }
}

function handleCancel() {
  // Reset to view mode
}

function handleClose() {
  router.push("/standards");
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
          <TreeNav
            :sections="store.sections"
            :selected-id="selectedItem?.id"
            :loading="store.loading"
            @select="handleSelect"
            @toggle="handleToggle"
            @add-child="handleAddChild"
            @add-root-section="handleAddRootSection"
            @close="handleClose"
          />
        </ResizablePanel>

        <!-- Resizable Handle -->
        <ResizableHandle
          with-handle
          class="w-1 bg-border hover:bg-primary/50 transition-colors"
        />

        <!-- Right Panel: Detail Canvas -->
        <ResizablePanel :default-size="75" class="min-w-[400px]">
          <DetailPanel
            :mode="viewMode"
            :standard="store.currentStandard"
            :selected-item="selectedItem"
            :parent-id="null"
            :selected-section-id="selectedSectionId"
            :loading="store.loading"
            :saving="store.loading"
            @update-standard="handleUpdateStandard"
            @update-section="handleUpdateSection"
            @update-requirement="handleUpdateRequirement"
            @delete-standard="handleDeleteStandard"
            @add-requirement="handleAddRequirement"
            @delete-requirement="handleDeleteReq"
            @delete="handleDelete"
            @save="handleCancel"
            @cancel="handleCancel"
          />
        </ResizablePanel>
      </ResizablePanelGroup>
    </div>

    <!-- Dialogs -->
    <StandardSectionDialog
      v-model:open="showSectionDialog"
      :section="null"
      :is-submitting="isSubmitting"
      @submit="handleCreateSectionSubmit"
    />

    <StandardRequirementDialog
      v-model:open="showRequirementDialog"
      :requirement="null"
      :is-submitting="isSubmitting"
      @submit="handleCreateRequirementSubmit"
    />

    <DeleteSectionDialog
      v-model:open="showDeleteDialog"
      :is-submitting="deleteMutationSelection.isPending.value"
      :section-name="selectedItem?.title"
      @confirm="handleDeleteSection"
    />

    <DeleteRequirementDialog
      v-model:open="showDeleteReqDialog"
      :is-submitting="deleteMutationRequirement.isPending.value"
      :requirement-name="selectedItem?.title"
      @confirm="handleDeleteRequirement"
    />
  </div>
</template>
