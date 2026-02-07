<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import type { StandardSection } from "@/features/standard/types/standard";
import {
  ChevronRight,
  ChevronDown,
  FileText,
  Folder,
  FolderOpen,
  Circle,
  Clock,
  AlertCircle,
  Check,
  ArrowRight,
} from "lucide-vue-next";
import { cn } from "@/lib/utils";
import { useAssessmentStore } from "../../stores/assessmentStore";
import { Badge } from "@/components/ui/badge";

// Props
const props = defineProps<{
  section: StandardSection;
  level?: number;
  searchQuery?: string;
}>();

// Store
const store = useAssessmentStore();

// State
const isOpen = ref(false);
const isFocused = ref(false);
const itemRef = ref<HTMLElement | null>(null);

// Computed
const hasChildren = computed(
  () =>
    (props.section.children && props.section.children.length > 0) ||
    (props.section.requirements && props.section.requirements.length > 0),
);

// Determine section icon based on type
const sectionIcon = computed(() => {
  switch (props.section.type) {
    case "domain":
      return isOpen.value ? FolderOpen : Folder;
    default:
      return FileText;
  }
});

// Get requirement status from store
const getRequirementStatus = (requirementId: string) => {
  const node = store.findNodeByRequirementId(store.treeData, requirementId);
  return node?.requirement?.status || null;
};

// Get requirement compliance status from store
const getRequirementComplianceStatus = (requirementId: string) => {
  const node = store.findNodeByRequirementId(store.treeData, requirementId);
  return node?.requirement?.complianceStatus || null;
};

// Check if text matches search query
const isHighlighted = (text: string) => {
  if (!props.searchQuery || !text) return false;
  return text.toLowerCase().includes(props.searchQuery.toLowerCase());
};

// Toggle section
const toggle = () => {
  if (hasChildren.value) {
    isOpen.value = !isOpen.value;
    // Sync with store
    store.toggleNode(props.section.id);
  }
};

// Keyboard navigation
const handleKeydown = (event: KeyboardEvent) => {
  switch (event.key) {
    case "Enter":
    case " ":
      event.preventDefault();
      if (hasChildren.value) {
        toggle();
      }
      break;
    case "ArrowRight":
      if (!isOpen.value && hasChildren.value) {
        toggle();
      }
      break;
    case "ArrowLeft":
      if (isOpen.value) {
        toggle();
      }
      break;
  }
};

// Focus management
const setFocus = (focused: boolean) => {
  isFocused.value = focused;
};

// Navigate to requirement
const setActiveRequirement = async (requirementId: string) => {
  const node = store.findNodeByRequirementId(store.treeData, requirementId);

  if (node) {
    expandParentNodes(store.treeData, node.id);
    await store.setActiveNode(node.id);
  }

  store.activeRequirementId = requirementId;
};

// Expand parent nodes helper
function expandParentNodes(
  nodes: any[],
  targetId: string,
  found = false,
): boolean {
  for (const node of nodes) {
    if (found || node.id === targetId) {
      return true;
    }

    if (node.children?.length) {
      const childFound = expandParentNodes(node.children, targetId, found);

      if (childFound && node.id !== targetId) {
        node.isExpanded = true;
        isOpen.value = true;
        return true;
      }

      if (childFound) return true;
    }
  }
  return false;
}

// Check if this section is active
const isActiveSection = computed(() => {
  return store.activeNodeId === props.section.id;
});

// Status badge for requirements
const getStatusBadge = (status: string | null) => {
  switch (status) {
    case "compliant":
      return {
        variant: "default" as const,
        icon: Check,
        color: "text-emerald-500",
      };
    case "partial":
      return {
        variant: "secondary" as const,
        icon: AlertCircle,
        color: "text-amber-500",
      };
    case "in_progress":
      return {
        variant: "outline" as const,
        icon: Clock,
        color: "text-blue-500",
      };
    default:
      return {
        variant: "outline" as const,
        icon: Circle,
        color: "text-muted-foreground",
      };
  }
};

// Count completed (fully compliant) requirements
const completedCount = computed(() => {
  return (
    props.section.requirements?.filter((r) => {
      const complianceStatus = getRequirementComplianceStatus(r.id);
      return complianceStatus === "fully_compliant";
    }).length || 0
  );
});

const totalRequirements = computed(() => {
  let count = props.section.requirements?.length || 0;
  props.section.children?.forEach((child) => {
    count += child.requirements?.length || 0;
  });
  return count;
});

// Keyboard shortcut for expansion
onMounted(() => {
  document.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
  <div ref="itemRef" class="select-none" :role="hasChildren ? 'group' : 'none'">
    <!-- Section Header -->
    <div
      @click="toggle"
      @keydown="handleKeydown"
      @focus="setFocus(true)"
      @blur="setFocus(false)"
      :class="
        cn(
          'flex items-center gap-2 py-2 px-3 rounded-lg cursor-pointer transition-all duration-200',
          'hover:bg-accent/50',
          isActiveSection ? 'bg-primary/10 text-primary' : '',
          isFocused ? 'ring-2 ring-primary/20 ring-offset-1' : '',
          hasChildren ? '' : 'cursor-default',
          (level || 0) > 0 ? 'ml-4' : '',
          'group/section',
        )
      "
      :tabindex="hasChildren ? 0 : -1"
      :aria-expanded="hasChildren ? isOpen : undefined"
      :aria-selected="isActiveSection"
    >
      <!-- Expand/Collapse Icon -->
      <Transition
        enter-active-class="transition-transform duration-200"
        leave-active-class="transition-transform duration-200"
        enter-from-class="transform -rotate-90"
        enter-to-class="transform rotate-0"
        leave-from-class="transform rotate-0"
        leave-to-class="transform -rotate-90"
      >
        <component
          :is="isOpen ? ChevronDown : ChevronRight"
          v-if="hasChildren"
          class="w-4 h-4 shrink-0 text-muted-foreground group-hover/section:text-foreground transition-colors"
        />
      </Transition>

      <!-- Section Icon -->
      <component
        :is="sectionIcon"
        :class="
          cn(
            'w-5 h-5 shrink-0 transition-colors',
            section.type === 'domain' ? 'text-amber-500' : 'text-blue-500',
          )
        "
      />

      <!-- Section Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <span
            :class="
              cn(
                'text-sm font-medium truncate',
                isActiveSection ? 'text-primary' : 'text-foreground',
              )
            "
          >
            {{ section.code }} {{ section.title }}
            <mark
              v-if="searchQuery && isHighlighted(section.title)"
              class="bg-yellow-200/50 dark:bg-yellow-800/30 rounded px-0.5"
            >
              {{ section.title }}
            </mark>
          </span>

          <!-- Domain Badge -->
          <Badge
            v-if="section.type === 'domain'"
            variant="secondary"
            class="text-[10px] px-1.5 py-0 h-5 shrink-0"
          >
            DOM
          </Badge>
        </div>

        <!-- Progress indicator -->
        <div v-if="totalRequirements > 0" class="flex items-center gap-2 mt-1">
          <div class="flex-1 h-1 bg-muted rounded-full overflow-hidden">
            <div
              class="h-full bg-emerald-500 transition-all duration-300"
              :style="{
                width: `${(completedCount / totalRequirements) * 100}%`,
              }"
            />
          </div>
          <span class="text-[10px] text-muted-foreground">
            {{ completedCount }}/{{ totalRequirements }}
          </span>
        </div>
      </div>

      <!-- Active indicator -->
      <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 translate-x-2"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition-all duration-200"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-2"
      >
        <ArrowRight
          v-if="isActiveSection && !hasChildren"
          class="w-4 h-4 shrink-0 text-primary"
        />
      </Transition>
    </div>

    <!-- Children & Requirements -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-[1000px]"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 max-h-[1000px]"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-if="isOpen" class="overflow-hidden">
        <!-- Nested Children -->
        <AssessmentSidebarItem
          v-for="child in section.children"
          :key="child.id"
          :section="child"
          :level="(level || 0) + 1"
          :search-query="searchQuery"
        />

        <!-- Requirements -->
        <div
          v-if="section.requirements?.length"
          class="mt-1 space-y-0.5"
          role="list"
          :aria-label="`Requirements for ${section.title}`"
        >
          <div
            v-for="req in section.requirements"
            :key="req.id"
            role="listitem"
          >
            <div
              @click.stop="setActiveRequirement(req.id)"
              @keydown.enter.stop="setActiveRequirement(req.id)"
              @focus="setFocus(true)"
              @blur="setFocus(false)"
              :class="
                cn(
                  'flex items-center gap-2 py-2 px-3 pr-4 rounded-md cursor-pointer transition-all duration-200',
                  'hover:bg-accent/30',
                  store.activeRequirementId === req.id
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:text-foreground',
                  isFocused ? 'ring-2 ring-primary/20 ring-offset-1' : '',
                  (level || 0) > 0 ? 'ml-8' : 'ml-8',
                  'group/requirement',
                )
              "
              :tabindex="0"
              :aria-selected="store.activeRequirementId === req.id"
            >
              <!-- Status Icon -->
              <div
                :class="
                  cn(
                    'w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors',
                    getRequirementComplianceStatus(req.id) === 'fully_compliant'
                      ? 'bg-emerald-100 dark:bg-emerald-900/30'
                      : getRequirementComplianceStatus(req.id) === 'partially_compliant'
                        ? 'bg-amber-100 dark:bg-amber-900/30'
                        : 'bg-muted',
                  )
                "
              >
                <component
                  :is="getStatusBadge(getRequirementStatus(req.id)).icon"
                  :class="
                    cn(
                      'w-3.5 h-3.5',
                      getStatusBadge(getRequirementStatus(req.id)).color,
                    )
                  "
                />
              </div>

              <!-- Requirement Info -->
              <div class="flex-1 min-w-0">
                <span
                  :class="
                    cn(
                      'text-sm truncate block',
                      store.activeRequirementId === req.id
                        ? 'font-medium text-primary'
                        : '',
                    )
                  "
                >
                  <template v-if="req.title">
                    {{ req.displayCode }} {{ req.title }}
                  </template>
                  <template v-else>
                    {{ req.displayCode }}
                  </template>
                  <mark
                    v-if="
                      searchQuery && isHighlighted(req.title || req.displayCode)
                    "
                    class="bg-yellow-200/50 dark:bg-yellow-800/30 rounded px-0.5"
                  >
                    {{ searchQuery }}
                  </mark>
                </span>
              </div>

              <!-- Active Indicator -->
              <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0 scale-50"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-50"
              >
                <div
                  v-if="store.activeRequirementId === req.id"
                  class="w-2 h-2 rounded-full bg-primary shrink-0"
                />
              </Transition>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Custom scrollbar for nested items */
div::-webkit-scrollbar {
  width: 4px;
}

div::-webkit-scrollbar-track {
  background: transparent;
}

div::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 2px;
}

div::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}
</style>
