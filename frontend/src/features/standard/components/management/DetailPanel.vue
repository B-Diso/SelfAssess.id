<script setup lang="ts">
import { ref, watch } from "vue";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";


import { Skeleton } from "@/components/ui/skeleton";

import type {
  Standard,
  StandardSection,
  StandardRequirement,
} from "../../types/standard";

import TiptapEditor from "@/components/ui/tiptap/TiptapEditor.vue";
import {
  Trash2,
  Save,
  Plus,
  History,
  LayoutDashboard,
  Info,
  ChevronRight,
  Layers,
  MousePointer2,
} from "lucide-vue-next";
import { Loader2 } from "lucide-vue-next";

interface Props {
  mode: "standard" | "section" | "requirement";
  standard?: Standard | null;
  selectedItem?: StandardSection | StandardRequirement | null;
  parentId?: string | null;
  selectedSectionId?: string | null;
  loading?: boolean;
  saving?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  saving: false,
});

const emit = defineEmits<{
  "update-standard": [data: Partial<Standard>];
  "update-section": [data: Partial<StandardSection>];
  "update-requirement": [data: Partial<StandardRequirement>];
  "delete-standard": [];
  "add-requirement": [section: StandardSection];
  "delete-requirement": [node: StandardRequirement];
  delete: [node: StandardSection];
  save: [];
  cancel: [];
}>();

// Form state for editing
const editForm = ref({
  // Standard fields
  name: props.standard?.name || "",
  version: props.standard?.version || "",
  description: props.standard?.description || "",
  periodType: props.standard?.periodType || "",

  // Section fields
  code: "",
  title: "",
  sectionDescription: "",
  level: 0,

  // Requirement fields
  displayCode: "",
  requirementTitle: "",
  requirementDescription: "",
  evidenceHint: "",
});

// Initialize form when selected item changes
watch(
  () => props.selectedItem,
  (item) => {
    if (!item) return;

    if (props.mode === "standard") {
      const standardItem = item as unknown as Standard;
      editForm.value = {
        ...editForm.value,
        name: standardItem.name || "",
        version: standardItem.version || "",
        description: standardItem.description || "",
        periodType: standardItem.periodType || "",
      };
    } else if (props.mode === "section") {
      const section = item as StandardSection;
      editForm.value = {
        ...editForm.value,
        code: section.code,
        title: section.title,
        sectionDescription: section.description || "",
        level: section.level || 0,
      };
    } else if (props.mode === "requirement") {
      const req = item as StandardRequirement;
      editForm.value = {
        ...editForm.value,
        displayCode: req.displayCode || "",
        requirementTitle: req.title || "",
        requirementDescription: req.description || "",
        evidenceHint: req.evidenceHint || "",
      };
    }
  },
  { immediate: true },
);

function handleSave() {
  if (props.mode === "standard") {
    emit("update-standard", {
      name: editForm.value.name,
      description: editForm.value.description || null,
      periodType: editForm.value.periodType || null,
    });
  } else if (props.mode === "section") {
    emit("update-section", {
      code: editForm.value.code,
      title: editForm.value.title,
      description: editForm.value.sectionDescription || null,
      level: editForm.value.level,
    });
  } else if (props.mode === "requirement") {
    emit("update-requirement", {
      displayCode: editForm.value.displayCode,
      title: editForm.value.requirementTitle,
      description: editForm.value.requirementDescription || null,
      evidenceHint: editForm.value.evidenceHint || null,
    });
  }
  emit("save");
}

function handleDelete() {
  if (props.mode === "standard") {
    emit("delete-standard");
  } else if (props.mode === "section" && props.selectedItem) {
    emit("delete", props.selectedItem as StandardSection);
  } else if (props.mode === "requirement" && props.selectedItem) {
    emit("delete-requirement", props.selectedItem as StandardRequirement);
  }
}

function formatDate(date?: string) {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function getDisplayTitle() {
  if (props.mode === "standard") {
    return props.standard?.name || "Standard Details";
  } else if (props.mode === "section") {
    const section = props.selectedItem as StandardSection | undefined;
    return section ? section.title : "Section Details";
  } else {
    const req = props.selectedItem as StandardRequirement | undefined;
    return req ? req.title : "Requirement Details";
  }
}

function getHierarchyContext() {
  if (props.mode === "standard") return "Root Level";

  if (props.mode === "section") {
    const section = props.selectedItem as StandardSection | undefined;
    if (!section) return "";
    return `Level ${section.level} • ${section.code}`;
  }

  const req = props.selectedItem as StandardRequirement | undefined;
  if (!req) return "";
  return `Requirement • ${req.displayCode}`;
}

function getUpdatedInfo() {
  if (props.mode === "standard") {
    return {
      date: props.standard?.updatedAt,
      user: "Admin User",
    };
  }
  return {
    date: (
      props.selectedItem as StandardSection | StandardRequirement | undefined
    )?.updatedAt,
    user: "Admin User",
  };
}
</script>

<template>
  <div class="flex flex-col h-full bg-background">
    <!-- Loading State -->
    <div v-if="loading" class="flex-1 p-8">
      <div class="max-w-3xl space-y-8">
        <div class="flex justify-between items-start">
          <div class="space-y-3">
            <Skeleton class="h-4 w-24" />
            <Skeleton class="h-10 w-64" />
          </div>
          <div class="flex gap-3">
            <Skeleton class="h-9 w-24" />
            <Skeleton class="h-9 w-32" />
          </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
          <Skeleton class="h-24 w-full" />
          <Skeleton class="h-24 w-full" />
          <Skeleton class="h-24 w-full" />
        </div>
        <div class="space-y-4">
          <Skeleton class="h-6 w-32" />
          <div class="grid grid-cols-2 gap-6">
            <Skeleton class="h-11 w-full" />
            <Skeleton class="h-11 w-full" />
          </div>
          <Skeleton class="h-64 w-full" />
        </div>
      </div>
    </div>

    <template v-else>
      <!-- Empty State when nothing selected -->
      <div
        v-if="!selectedItem && mode === 'standard'"
        class="flex-1 flex flex-col items-center justify-center p-8 text-center animate-in fade-in zoom-in duration-300"
      >
        <div
          class="h-20 w-20 rounded-full bg-muted/50 flex items-center justify-center mb-6"
        >
          <MousePointer2 class="h-10 w-10 text-muted-foreground/60" />
        </div>
        <h2 class="text-2xl font-bold tracking-tight mb-2">
          Welcome to Standard Management
        </h2>
        <p class="text-muted-foreground max-w-sm mb-8 leading-relaxed">
          Please select a domain, section, or requirement from the tree
          structure on the left to view and modify its properties.
        </p>
        <div class="grid grid-cols-2 gap-4 w-full max-w-md">
          <div class="p-4 rounded-xl border bg-card/50 text-left space-y-2">
            <div
              class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground"
            >
              <Layers class="h-3 w-3" />
              Hierarchy
            </div>
            <p class="text-sm text-muted-foreground">
              Manage domains and nested sections efficiently.
            </p>
          </div>
          <div class="p-4 rounded-xl border bg-card/50 text-left space-y-2">
            <div
              class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground"
            >
              <Info class="h-3 w-3" />
              Requirements
            </div>
            <p class="text-sm text-muted-foreground">
              Define and edit scoring logic for specific criteria.
            </p>
          </div>
        </div>
      </div>

      <!-- Detail View when an item is selected -->
      <template v-else>
        <!-- Detail Header -->
        <div class="px-8 py-6 border-b flex justify-between items-start gap-4">
          <div class="space-y-1">
            <div
              class="flex items-center gap-2 text-xs font-medium text-muted-foreground uppercase tracking-wider"
            >
              <template v-if="mode !== 'standard'">
                <span>Standard</span>
                <ChevronRight class="h-3 w-3" />
              </template>
              <span>{{ getHierarchyContext() }}</span>
            </div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold tracking-tight">
                {{ getDisplayTitle() }}
              </h1>
            </div>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <Button
              v-if="mode === 'section'"
              variant="outline"
              size="sm"
              @click="emit('add-requirement', selectedItem as StandardSection)"
            >
              <Plus class="h-4 w-4 mr-2" />
              Add Requirement 
            </Button>
            <Button variant="destructive" size="sm" @click="handleDelete">
              <Trash2 class="h-4 w-4 mr-2" />
              Delete
            </Button>
            <Button
              class="bg-black hover:bg-black/90 text-white"
              size="sm"
              :disabled="saving"
              @click="handleSave"
            >
              <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
              <Save v-else class="h-4 w-4 mr-2" />
              Save
            </Button>
          </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden">
          <div class="p-8">
            <div class="max-w-3xl space-y-8">
              <!-- Standard Root Dashboard Mode -->
              <template v-if="mode === 'standard'">
                <div class="space-y-8">
                  <!-- Summary Cards -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                      class="p-4 rounded-xl border bg-card shadow-sm space-y-2"
                    >
                      <div
                        class="flex items-center gap-2 text-muted-foreground text-sm font-medium"
                      >
                        <LayoutDashboard class="h-4 w-4" />
                        Standard Type
                      </div>
                      <div class="text-lg font-bold capitalize">
                        {{ standard?.type || "N/A" }}
                      </div>
                    </div>

                    <div
                      class="p-4 rounded-xl border bg-card shadow-sm space-y-2"
                    >
                      <div
                        class="flex items-center gap-2 text-muted-foreground text-sm font-medium"
                      >
                        <Layers class="h-4 w-4" />
                        Version
                      </div>
                      <div class="text-lg font-bold">
                        {{ standard?.version || "1.0" }}
                      </div>
                    </div>

                    <div
                      class="p-4 rounded-xl border bg-card shadow-sm space-y-2"
                    >
                      <div
                        class="flex items-center gap-2 text-muted-foreground text-sm font-medium"
                      >
                        <History class="h-4 w-4" />
                        Period
                      </div>
                      <div class="text-lg font-bold capitalize">
                        {{ standard?.periodType || "Annual" }}
                      </div>
                    </div>
                  </div>

                  <div class="space-y-6 pt-4">
                    <div class="flex items-center gap-2 text-lg font-semibold">
                      <Info class="h-5 w-5 text-primary" />
                      Core Information
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <Label
                          for="name"
                          class="text-xs font-bold uppercase text-muted-foreground"
                          >Standard Name</Label
                        >
                        <Input
                          id="name"
                          v-model="editForm.name"
                          class="h-11 shadow-sm"
                        />
                      </div>
                      <div class="space-y-2">
                        <Label
                          for="version"
                          class="text-xs font-bold uppercase text-muted-foreground"
                          >Version Tag</Label
                        >
                        <Input
                          id="version"
                          v-model="editForm.version"
                          disabled
                          class="h-11 bg-muted/50"
                        />
                      </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <Label
                          for="periodType"
                          class="text-xs font-bold uppercase text-muted-foreground"
                          >Period Type</Label
                        >
                        <Input
                          id="periodType"
                          v-model="editForm.periodType"
                          placeholder="e.g., annual, quarterly"
                          class="h-11 shadow-sm"
                        />
                      </div>
                    </div>

                    <div class="space-y-3">
                      <Label
                        for="description"
                        class="text-xs font-bold uppercase text-muted-foreground"
                        >Detailed Description</Label
                      >
                      <div
                        class="rounded-lg border shadow-sm overflow-hidden bg-card"
                      >
                        <TiptapEditor
                          v-model="editForm.description"
                          placeholder="Enter detailed standard overview..."
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Section Mode (Generic Hierarchy) -->
              <template v-else-if="mode === 'section'">
                <div class="space-y-8">
                  <div class="space-y-2">
                    <Label
                      for="section-code"
                      class="text-xs font-bold uppercase text-muted-foreground"
                      >Node Code</Label
                    >
                    <Input
                      id="section-code"
                      v-model="editForm.code"
                      placeholder="e.g., D1, 1.1, S.1"
                      class="h-11 shadow-sm"
                    />
                  </div>

                  <div class="space-y-2">
                    <Label
                      for="section-title"
                      class="text-xs font-bold uppercase text-muted-foreground"
                      >Node Title</Label
                    >
                    <Input
                      id="section-title"
                      v-model="editForm.title"
                      placeholder="Enter node title"
                      class="h-11 font-semibold shadow-sm"
                    />
                  </div>

                  <div class="space-y-3">
                    <Label
                      for="section-description"
                      class="text-xs font-bold uppercase text-muted-foreground"
                      >Node Description</Label
                    >
                    <div
                      class="rounded-lg border shadow-sm overflow-hidden bg-card"
                    >
                      <TiptapEditor
                        v-model="editForm.sectionDescription"
                        placeholder="Enter detailed description for this hierarchy node..."
                      />
                    </div>
                  </div>
                </div>
              </template>

              <!-- Requirement Mode - Direct Form (No Tabs) -->
              <template v-else-if="mode === 'requirement'">
                <div class="space-y-6">
                  <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                      <Label
                        for="display-code"
                        class="text-xs font-bold uppercase text-muted-foreground"
                        >Display Code</Label
                      >
                      <Input
                        id="display-code"
                        v-model="editForm.displayCode"
                        placeholder="e.g., REQ-1.1"
                        class="h-11 shadow-sm"
                      />
                    </div>

                    <div class="space-y-2">
                      <Label
                        for="req-title"
                        class="text-xs font-bold uppercase text-muted-foreground"
                        >Title</Label
                      >
                      <Input
                        id="req-title"
                        v-model="editForm.requirementTitle"
                        placeholder="Enter requirement title"
                        class="h-11 shadow-sm font-semibold"
                      />
                    </div>

                    <div class="space-y-3">
                      <Label
                        for="req-description"
                        class="text-xs font-bold uppercase text-muted-foreground"
                        >Description</Label
                      >
                      <div
                        class="rounded-lg border shadow-sm overflow-hidden bg-card"
                      >
                        <TiptapEditor
                          id="req-description"
                          v-model="editForm.requirementDescription"
                          placeholder="Enter requirement description..."
                        />
                      </div>
                    </div>

                    <div class="space-y-3">
                      <Label
                        for="evidence-hint"
                        class="text-xs font-bold uppercase text-muted-foreground"
                        >Evidence Hint</Label
                      >
                      <div
                        class="rounded-lg border shadow-sm overflow-hidden bg-card"
                      >
                        <TiptapEditor
                          id="evidence-hint"
                          v-model="editForm.evidenceHint"
                          placeholder="Provide guidance on what evidence is needed..."
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Footer Audit Log -->
        <div
          class="px-8 py-3 border-t bg-muted/30 flex justify-between items-center text-xs text-muted-foreground"
        >
          <div class="flex items-center gap-2">
            <History class="h-4 w-4" />
            <span>
              Last updated on
              <span class="font-medium">{{
                formatDate(getUpdatedInfo().date)
              }}</span>
              by
              <span class="font-medium">{{ getUpdatedInfo().user }}</span>
            </span>
          </div>
          <div>Version 1.0</div>
        </div>
      </template>
    </template>
  </div>
</template>
