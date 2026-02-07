<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ResizablePanel,
  ResizableHandle,
  ResizablePanelGroup,
} from "@/components/ui/resizable";
import { toast } from "vue-sonner";

import TreeNav from "../components/management/TreeNav.vue";
import DetailPanel from "../components/management/DetailPanel.vue";
import { useStandardStore } from "../stores/standardStore";
import type {
  Standard,
  StandardSection,
  StandardRequirement,
} from "../types/standard";

const route = useRoute();
const router = useRouter();
const store = useStandardStore();

const standardId = computed(() => route.params.id as string);
const isCreating = computed(() => route.name === "standards.create");

// UI State
const viewMode = ref<"standard" | "section" | "requirement">("standard");
const selectedSectionId = ref<string | null>(null);
const selectedItem = ref<StandardSection | StandardRequirement | null>(null);

// Fetch data on mount
onMounted(async () => {
  if (!isCreating.value && standardId.value) {
    await loadStandardData();
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
    viewMode.value = "standard";
    return;
  }

  if ("displayCode" in node) {
    viewMode.value = "requirement";
    selectedItem.value = node;
    selectedSectionId.value = (node as StandardRequirement).standardSectionId;
  } else {
    viewMode.value = "section";
    selectedItem.value = node as StandardSection;
    selectedSectionId.value = node.id;
  }
}

function handleToggle(nodeId: string) {
  store.toggleExpandedNode(nodeId);
}

function handleAddChild(parent: StandardSection) {
  // TODO: Open dialog to add child section
  toast.info(`Add child to ${parent.title} - to be implemented`);
}

async function handleDelete(node: StandardSection) {
  if (!confirm(`Are you sure you want to delete "${node.title}"?`)) {
    return;
  }

  try {
    await store.deleteSection(node.id);
    toast.success("Section deleted successfully");

    // Reset selection if deleted item was selected
    if (selectedItem.value?.id === node.id) {
      selectedItem.value = null;
      viewMode.value = "standard";
    }
  } catch (error) {
    toast.error("Failed to delete section");
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
            @delete="handleDelete"
            @save="handleCancel"
            @cancel="handleCancel"
          />
        </ResizablePanel>
      </ResizablePanelGroup>
    </div>
  </div>
</template>
