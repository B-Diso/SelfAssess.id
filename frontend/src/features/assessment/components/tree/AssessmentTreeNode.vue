<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { cn } from "@/lib/utils";
import type { AssessmentTreeNode as TreeNodeType } from "../../types/assessment.types";
import type { AssessmentResponseStatus } from "../../types/assessment-response.types";
import {
  ChevronRight,
  ChevronDown,
  Folder,
  FolderOpen,
  FileText,
  CheckCircle2,
  Circle,
  AlertCircle,
  AlertTriangle,
  Ban,
} from "lucide-vue-next";
import { Badge } from "@/components/ui/badge";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";

interface Props {
  node: TreeNodeType;
  selectedId?: string;
  expanded: boolean;
  expandedNodes?: Set<string>;
  level?: number;
}

const props = withDefaults(defineProps<Props>(), {
  level: 0,
});

const emit = defineEmits<{
  select: [nodeId: string];
  toggle: [nodeId: string];
}>();

const isExpanded = ref(props.expanded);

watch(
  () => props.expanded,
  (newVal) => {
    isExpanded.value = newVal;
  },
);

const isSelected = computed(() => props.selectedId === props.node.id);

const hasChildren = computed(
  () => props.node.children && props.node.children.length > 0,
);

// Determine response workflow status for requirements (active, pending_review, reviewed)
const responseStatus = computed<AssessmentResponseStatus | null>(() => {
  if (props.node.type !== "requirement" || !props.node.requirement) {
    return null;
  }
  return props.node.requirement.status || null;
});

// Determine node status for requirements (compliance status)
const nodeStatus = computed(() => {
  if (props.node.type !== "requirement" || !props.node.requirement) {
    return null;
  }

  const complianceStatus = props.node.requirement.complianceStatus;

  // Map compliance status directly to match RequirementComplianceForm
  switch (complianceStatus) {
    case "fully_compliant":
      return "fully_compliant"; // CheckCircle2 - Green
    case "partially_compliant":
      return "partially_compliant"; // AlertTriangle - Amber
    case "non_compliant":
      return "non_compliant"; // AlertCircle - Red
    case "not_applicable":
      return "not_applicable"; // Ban - Slate
    default:
      return "pending"; // Circle - default
  }
});

// Professional calm color palette based on hierarchy level
interface LevelColor {
  icon: string;
  text: string;
  hover: string;
  selected: string;
  badge: string;
}

const colorPalette: Record<number, LevelColor> = {
  0: {
    icon: "text-slate-600",
    text: "text-slate-900",
    hover: "hover:bg-slate-100/80",
    selected: "bg-blue-600 text-white font-semibold shadow-md",
    badge: "bg-slate-100 text-slate-700 border-slate-300",
  },
  1: {
    icon: "text-slate-500",
    text: "text-slate-700",
    hover: "hover:bg-slate-50/60",
    selected: "bg-blue-500 text-white font-medium shadow-md",
    badge: "bg-slate-50 text-slate-600 border-slate-200",
  },
  2: {
    icon: "text-slate-400",
    text: "text-slate-600",
    hover: "hover:bg-gray-50/40",
    selected: "bg-blue-500 text-white shadow-md",
    badge: "bg-gray-50 text-slate-500 border-slate-100",
  },
  3: {
    icon: "text-slate-400",
    text: "text-slate-500",
    hover: "hover:bg-gray-25/50",
    selected: "bg-blue-500 text-white shadow-md",
    badge: "bg-gray-50 text-slate-400 border-gray-100",
  },
};

const levelColors = computed((): LevelColor => {
  // Requirements use colors based on compliance status only
  if (props.node.type === "requirement") {
    switch (nodeStatus.value) {
      case "fully_compliant":
        return {
          icon: "text-green-600",
          text: "text-slate-700",
          hover: "hover:bg-green-50/60",
          selected: "bg-green-600 text-white font-medium shadow-md",
          badge: "bg-green-50 border-green-200",
        };
      case "partially_compliant":
        return {
          icon: "text-amber-600",
          text: "text-slate-700",
          hover: "hover:bg-amber-50/60",
          selected: "bg-amber-600 text-white font-medium shadow-md",
          badge: "bg-amber-50 border-amber-200",
        };
      case "non_compliant":
        return {
          icon: "text-red-600",
          text: "text-slate-700",
          hover: "hover:bg-red-50/60",
          selected: "bg-red-600 text-white font-medium shadow-md",
          badge: "bg-red-50 border-red-200",
        };
      case "not_applicable":
        return {
          icon: "text-slate-500",
          text: "text-slate-700",
          hover: "hover:bg-slate-50/60",
          selected: "bg-slate-600 text-white font-medium shadow-md",
          badge: "bg-slate-50 border-slate-200",
        };
      default:
        return {
          icon: "text-slate-400",
          text: "text-slate-700",
          hover: "hover:bg-slate-50/60",
          selected: "bg-slate-500 text-white font-medium shadow-md",
          badge: "bg-slate-50 border-slate-200",
        };
    }
  }

  const level = props.level ?? 0;
  return colorPalette[level] ?? colorPalette[3]!;
});

const nodeIcon = computed(() => {
  if (props.node.type === "requirement") {
    // Icon always follows compliance status
    switch (nodeStatus.value) {
      case "fully_compliant":
        return CheckCircle2;
      case "partially_compliant":
        return AlertTriangle;
      case "non_compliant":
        return AlertCircle;
      case "not_applicable":
        return Ban;
      default:
        return Circle;
    }
  }

  if (
    props.node.type === "section" ||
    props.node.type === "domain" ||
    props.node.type === "element"
  ) {
    return isExpanded.value ? FolderOpen : Folder;
  }

  return FileText;
});

// Left border indicator for response workflow status
const borderIndicator = computed(() => {
  if (props.node.type !== "requirement" || !responseStatus.value) {
    return "";
  }

  switch (responseStatus.value) {
    case "pending_review":
      return "border-l-4 border-l-blue-500";
    case "reviewed":
      return "border-l-4 border-l-green-500";
    default:
      return "";
  }
});

function toggleExpanded() {
  if (hasChildren.value) {
    emit("toggle", props.node.id);
  }
}

function handleSelect() {
  emit("select", props.node.id);
}
</script>

<template>
  <div class="tree-node" :data-node-id="node.id">
    <!-- Node Content -->
    <div
      :class="
        cn(
          'flex items-center gap-1.5 px-2 py-1 select-none rounded-md cursor-pointer transition-all duration-150 text-[11px] group',
          borderIndicator,
          isSelected
            ? levelColors.selected
            : `${levelColors.text} ${levelColors.hover}`,
        )
      "
      @click="handleSelect"
    >
      <!-- Expand/Collapse Button -->
      <div
        v-if="hasChildren"
        class="h-5 w-5 shrink-0 flex items-center justify-center hover:bg-black/5 rounded transition-colors cursor-pointer"
        @click.stop="toggleExpanded"
      >
        <ChevronDown
          v-if="isExpanded"
          :class="
            cn('h-3.5 w-3.5', isSelected ? 'text-white' : levelColors.icon)
          "
        />
        <ChevronRight
          v-else
          :class="
            cn('h-3.5 w-3.5', isSelected ? 'text-white' : levelColors.icon)
          "
        />
      </div>

      <!-- Placeholder for alignment when no children -->
      <div v-else class="h-5 w-5 shrink-0" />

      <!-- Node Icon -->
      <component
        :is="nodeIcon"
        class="h-4 w-4 shrink-0 transition-transform duration-200"
        :class="[
          isSelected ? 'text-white scale-110' : levelColors.icon,
        ]"
      />

      <!-- Node Label -->
      <div class="flex-1 flex items-center gap-2 truncate">
        <Badge
          variant="outline"
          :class="
            cn(
              'px-1.5 py-0 h-5 text-[10px] font-mono font-bold shrink-0 border',
              isSelected
                ? 'text-white border-white/30 bg-white/20'
                : levelColors.badge,
            )
          "
        >
          {{ node.code }}
        </Badge>

        <TooltipProvider>
          <Tooltip :delay-duration="0">
            <TooltipTrigger as-child>
              <span
                class="truncate font-medium transition-colors"
                :class="
                  level === 0
                    ? 'text-[12px] font-semibold'
                    : 'text-[11px] font-medium'
                "
              >
                {{ node.title }}
              </span>
            </TooltipTrigger>
            <TooltipContent side="bottom" class="max-w-xs">
              <div class="space-y-1.5">
                <p class="font-bold text-sm">{{ node.code }}</p>
                <p class="text-xs">{{ node.title }}</p>

                <!-- Requirement Status Info -->
                <template v-if="node.type === 'requirement'">
                  <!-- Workflow Status (response) -->
                  <p
                    v-if="responseStatus"
                    class="text-[10px] uppercase font-bold pt-1 border-t flex items-center gap-2"
                  >
                    <span
                      v-if="responseStatus === 'pending_review'"
                      class="w-2 h-2 rounded-full bg-blue-500"
                    />
                    <span
                      v-else-if="responseStatus === 'reviewed'"
                      class="w-2 h-2 rounded-full bg-green-500"
                    />
                    <span
                      v-else
                      class="w-2 h-2 rounded-full bg-slate-400"
                    />
                    {{ responseStatus.replace(/_/g, " ") }}
                  </p>

                  <!-- Compliance Status -->
                  <p
                    v-if="nodeStatus"
                    class="text-[10px] opacity-90 uppercase font-semibold flex items-center gap-2"
                  >
                    <span class="text-muted-foreground">Compliance:</span>
                    {{ nodeStatus.replace(/_/g, " ") }}
                  </p>
                </template>
              </div>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>

    </div>

    <!-- Children -->
    <Transition name="expand">
      <div v-if="isExpanded && hasChildren" class="node-children">
        <AssessmentTreeNode
          v-for="child in node.children"
          :key="child.id"
          :node="child"
          :selected-id="selectedId"
          :expanded="expandedNodes?.has(child.id) ?? false"
          :expanded-nodes="expandedNodes"
          :level="(level || 0) + 1"
          @select="emit('select', $event)"
          @toggle="emit('toggle', $event)"
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.tree-node {
  @apply list-none;
}

.node-children {
  position: relative;
  padding-left: 1.125rem;
}

/* Indentation guide line */
.node-children::before {
  content: "";
  position: absolute;
  left: 0.55rem;
  top: 0;
  bottom: 0.5rem;
  width: 1px;
  background-color: #cbd5e1;
}

.expand-enter-active,
.expand-leave-active {
  transition: all 0.2s ease-in-out;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 2000px;
}
</style>
