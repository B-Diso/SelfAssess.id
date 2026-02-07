<script setup lang="ts">
import { ref, computed, watch, nextTick } from "vue";
import { Expand, Shrink, Plus, Search, X } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import TreeNode from "./TreeNode.vue";
import type { StandardSection } from "../../types/standard";
import { useStandardStore } from "../../stores/standardStore";

interface Props {
  sections: StandardSection[];
  selectedId?: string;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<{
  select: [node: StandardSection | null];
  addChild: [parent: StandardSection];
  "add-root-section": [];
  close: [];
  toggle: [nodeId: string];
}>()

const store = useStandardStore();
const searchQuery = ref("");

// Use expandedNodes from store to preserve state across operations
const expandedNodes = computed({
  get: () => store.expandedNodes,
  set: () => {} // readonly, managed by store
});

// Scroll to selected node when it changes
watch(
  () => props.selectedId,
  async (newId) => {
    if (newId) {
      await nextTick();
      requestAnimationFrame(() => {
        const element = document.querySelector(`[data-node-id="${newId}"]`);
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    }
  },
  { immediate: false }
);

const hasSections = computed(() => props.sections && props.sections.length > 0);

// Calculate stats
const stats = computed(() => {
  let sections = 0;
  let requirements = 0;

  function countNodes(nodes: StandardSection[]) {
    for (const node of nodes) {
      sections++;
      if (node.requirements) {
        requirements += node.requirements.length;
      }
      if (node.children?.length) {
        countNodes(node.children);
      }
    }
  }

  countNodes(props.sections);
  return { sections, requirements };
});

const filteredSections = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.sections;
  }

  const query = searchQuery.value.toLowerCase();

  function filterNode(node: StandardSection): StandardSection | null {
    const matchesTitle = node.title.toLowerCase().includes(query);
    const matchesCode = node.code.toLowerCase().includes(query);

    // Check requirements
    const matchingRequirements = node.requirements?.filter(
      (req) =>
        (req.title || "").toLowerCase().includes(query) ||
        (req.displayCode || "").toLowerCase().includes(query),
    );

    // Check children
    const matchingChildren = node.children
      ?.map((child) => filterNode(child))
      .filter((child): child is StandardSection => child !== null);

    if (
      matchesTitle ||
      matchesCode ||
      (matchingRequirements && matchingRequirements.length > 0) ||
      (matchingChildren && matchingChildren.length > 0)
    ) {
      return {
        ...node,
        children: matchingChildren,
        requirements: matchingRequirements,
      };
    }

    return null;
  }

  return props.sections
    .map((section) => filterNode(section))
    .filter((section): section is StandardSection => section !== null);
});

function expandAll() {
  const allIds = getAllNodeIds(props.sections);
  store.expandAll(allIds);
}

function collapseAll() {
  store.collapseAll();
}

function getAllNodeIds(nodes: StandardSection[]): string[] {
  const ids: string[] = [];

  for (const node of nodes) {
    ids.push(node.id);
    if (node.children && node.children.length > 0) {
      ids.push(...getAllNodeIds(node.children));
    }
  }

  return ids;
}

function handleSelect(node: StandardSection | null) {
  // Toggle: klik item yang sama = unselect (emit null)
  if (node === null || props.selectedId === node.id) {
    emit("select", null);
  } else {
    emit("select", node);
  }
}
</script>

<template>
  <div class="flex flex-col h-full bg-background border-r">
    <!-- Header - Styling serupa Assessment TreeNav -->
    <div class="p-3 border-b space-y-2">
      <!-- Row 1: Title + Stats + Create Button -->
      <div class="flex items-center justify-between gap-2">
        <div class="min-w-0 flex-1">
          <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground truncate">
            Standard Structure
          </h2>
          <div class="text-[10px] text-muted-foreground mt-0.5">
            <span class="font-medium text-slate-700">{{ stats.sections }}</span>
            <span class="text-muted-foreground/70"> sections</span>
            <span class="mx-1">•</span>
            <span class="font-medium text-amber-600">{{ stats.requirements }}</span>
            <span class="text-muted-foreground/70"> requirements</span>
          </div>
        </div>
        
        <!-- Add Root Section Button -->
        <TooltipProvider>
          <Tooltip :delay-duration="0">
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                size="icon"
                class="h-7 w-7 shrink-0"
                @click="emit('add-root-section')"
              >
                <Plus class="h-4 w-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent>Add Root Section</TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <!-- Close Button -->
        <TooltipProvider>
          <Tooltip :delay-duration="0">
            <TooltipTrigger as-child>
              <Button
                variant="ghost"
                size="icon"
                class="h-7 w-7 shrink-0 text-muted-foreground"
                @click="emit('close')"
              >
                <X class="h-4 w-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent>Back to Standards List</TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>

      <!-- Row 2: Search + View Controls -->
      <div class="flex items-center gap-1.5">
        <!-- Search -->
        <div class="relative flex-1 min-w-0">
          <Search
            class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground"
          />
          <Input
            v-model="searchQuery"
            placeholder="Filter sections..."
            class="pl-8 h-7 text-xs"
          />
        </div>

        <!-- View Controls -->
        <div class="flex items-center gap-0.5 shrink-0">
          <!-- Expand All -->
          <TooltipProvider>
            <Tooltip :delay-duration="0">
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7"
                  @click="expandAll"
                >
                  <Expand class="h-3.5 w-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipContent>Expand All</TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <!-- Collapse All -->
          <TooltipProvider>
            <Tooltip :delay-duration="0">
              <TooltipTrigger as-child>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7"
                  @click="collapseAll"
                >
                  <Shrink class="h-3.5 w-3.5" />
                </Button>
              </TooltipTrigger>
              <TooltipContent>Collapse All</TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      </div>
    </div>

    <!-- Tree Content -->
    <div class="flex-1 h-full overflow-y-auto overflow-x-hidden">
      <div class="p-2">
        <!-- Loading State -->
        <div v-if="loading" class="space-y-3 p-2">
          <div v-for="i in 6" :key="i" class="flex items-center gap-3">
            <Skeleton class="h-4 w-4" />
            <Skeleton class="h-8 flex-1" />
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!hasSections" class="text-center py-8">
          <p class="text-sm text-muted-foreground mb-3">No sections yet</p>
        </div>

        <!-- Tree Nodes -->
        <div v-else-if="filteredSections.length > 0" class="space-y-0.5">
          <TreeNode
            v-for="section in filteredSections"
            :key="section.id"
            :node="section"
            :selected-id="selectedId"
            :expanded="expandedNodes.has(section.id)"
            :expanded-nodes="expandedNodes"
            @select="handleSelect"
            @add-child="emit('addChild', $event)"
          />
        </div>

        <!-- No Search Results -->
        <div v-else class="text-center py-8 text-muted-foreground">
          <p class="text-sm">No results found</p>
        </div>
      </div>
    </div>
  </div>
</template>
