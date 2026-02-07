<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { standardApi } from "@/features/standard/api/standardApi";
import type { StandardSection } from "@/features/standard/types/standard";
import AssessmentSidebarItem from "./AssessmentSidebarItem.vue";
import { ScrollArea } from "@/components/ui/scroll-area";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { Loader2, ListTree, Search, X } from "lucide-vue-next";
import { Badge } from "@/components/ui/badge";

const sections = ref<StandardSection[]>([]);
const loading = ref(false);
const assessmentStore = useAssessmentStore();
const searchQuery = ref("");
const showSearch = ref(false);

// Filter sections based on search query
const filteredSections = computed(() => {
  if (!searchQuery.value.trim()) return sections.value;

  const query = searchQuery.value.toLowerCase();

  const filterSection = (section: StandardSection): StandardSection | null => {
    const matchesCode = section.code.toLowerCase().includes(query);
    const matchesTitle = section.title.toLowerCase().includes(query);

    // Filter requirements
    const filteredRequirements =
      section.requirements?.filter(
        (req) =>
          req.displayCode.toLowerCase().includes(query) ||
          req.title?.toLowerCase().includes(query),
      ) || [];

    // Filter children recursively
    const filteredChildren =
      (section.children
        ?.map(filterSection)
        .filter(Boolean) as StandardSection[]) || [];

    // Include section if it matches, or has matching children/requirements
    if (
      matchesCode ||
      matchesTitle ||
      filteredChildren.length > 0 ||
      filteredRequirements.length > 0
    ) {
      return {
        ...section,
        children:
          filteredChildren.length > 0 ? filteredChildren : section.children,
        requirements:
          filteredRequirements.length > 0
            ? filteredRequirements
            : section.requirements,
      };
    }

    return null;
  };

  return sections.value.map(filterSection).filter(Boolean) as StandardSection[];
});

// Get total sections count
const totalSections = computed(() => {
  const countSections = (items: StandardSection[]): number => {
    return items.reduce((acc, section) => {
      acc += 1;
      if (section.children?.length) {
        acc += countSections(section.children);
      }
      return acc;
    }, 0);
  };
  return countSections(sections.value);
});

// Get total requirements count
const totalRequirements = computed(() => {
  const countRequirements = (items: StandardSection[]): number => {
    return items.reduce((acc, section) => {
      acc += section.requirements?.length || 0;
      if (section.children?.length) {
        acc += countRequirements(section.children);
      }
      return acc;
    }, 0);
  };
  return countRequirements(sections.value);
});

const clearSearch = () => {
  searchQuery.value = "";
  showSearch.value = false;
};

watch(
  () => assessmentStore.assessment?.standardId,
  async (newId) => {
    if (newId) {
      loading.value = true;
      try {
        sections.value = await standardApi.getSections(newId);
      } catch (e) {
        console.error("Failed to load sections:", e);
      } finally {
        loading.value = false;
      }
    }
  },
  { immediate: true },
);
</script>

<template>
  <div class="flex flex-col h-full bg-card border-r min-h-0 shadow-sm">
    <!-- Header -->
    <div
      class="p-4 border-b bg-gradient-to-b from-background to-muted/20 shrink-0"
    >
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <div class="p-2 rounded-lg bg-primary/10">
            <ListTree class="w-5 h-5 text-primary" />
          </div>
          <div>
            <h2 class="font-semibold text-sm">Navigation</h2>
            <p class="text-xs text-muted-foreground">
              {{ totalSections }} sections ·
              {{ totalRequirements }} requirements
            </p>
          </div>
        </div>
        <Loader2
          v-if="loading"
          class="w-4 h-4 animate-spin text-muted-foreground"
        />
      </div>

      <!-- Search Toggle -->
      <button
        v-if="!showSearch"
        @click="showSearch = true"
        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground bg-muted/50 hover:bg-muted rounded-md transition-colors"
      >
        <Search class="w-4 h-4" />
        <span>Search sections...</span>
        <kbd class="ml-auto text-xs bg-background px-1.5 py-0.5 rounded border"
          >⌘K</kbd
        >
      </button>

      <!-- Search Input -->
      <div v-else class="relative">
        <Search
          class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"
        />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search sections..."
          class="w-full pl-9 pr-8 py-2 text-sm bg-background border rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20"
          autofocus
        />
        <button
          @click="clearSearch"
          class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-muted rounded"
        >
          <X class="w-3.5 h-3.5 text-muted-foreground" />
        </button>
      </div>
    </div>

    <!-- Progress Bar -->
    <div v-if="assessmentStore.summary" class="px-4 py-2 border-b bg-muted/10">
      <div class="flex items-center justify-between text-xs mb-1">
        <span class="text-muted-foreground">Progress</span>
        <span class="font-medium"
          >{{ assessmentStore.progressPercentage }}%</span
        >
      </div>
      <div class="h-1.5 bg-muted rounded-full overflow-hidden">
        <div
          class="h-full bg-gradient-to-r from-primary to-primary/80 transition-all duration-500 ease-out"
          :style="{ width: `${assessmentStore.progressPercentage}%` }"
        />
      </div>
    </div>

    <!-- Navigation Items -->
    <ScrollArea class="flex-1 min-h-0">
      <div class="p-2">
        <TransitionGroup name="sidebar-list" tag="div" class="space-y-1">
          <AssessmentSidebarItem
            v-for="section in filteredSections"
            :key="section.id"
            :section="section"
            :search-query="searchQuery"
          />
        </TransitionGroup>

        <!-- Empty State -->
        <div
          v-if="!loading && filteredSections.length === 0"
          class="flex flex-col items-center justify-center py-8 text-center"
        >
          <div class="p-3 rounded-full bg-muted mb-3">
            <Search class="w-5 h-5 text-muted-foreground" />
          </div>
          <p class="text-sm font-medium">No sections found</p>
          <p class="text-xs text-muted-foreground mt-1">
            Try adjusting your search
          </p>
        </div>
      </div>
    </ScrollArea>

    <!-- Footer -->
    <div class="p-3 border-t bg-muted/30 text-xs text-muted-foreground">
      <div class="flex items-center justify-between">
        <span>Assessment Navigation</span>
        <Badge variant="secondary" class="text-[10px] px-1.5 py-0">
          v1.0
        </Badge>
      </div>
    </div>
  </div>
</template>

<style scoped>
.sidebar-list-enter-active,
.sidebar-list-leave-active {
  transition: all 0.2s ease;
}

.sidebar-list-enter-from,
.sidebar-list-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}

.sidebar-list-move {
  transition: transform 0.2s ease;
}
</style>
