<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { cn } from "@/lib/utils";
import type { StandardSection } from "../../types/standard";
import {
  ChevronRight,
  ChevronDown,
  Folder,
  FolderOpen,
  Plus,
  CheckCircle2,
  Circle,
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";

interface Props {
  node: StandardSection;
  selectedId?: string;
  expanded: boolean;
  expandedNodes?: Set<string>;
  level?: number;
}

const props = withDefaults(defineProps<Props>(), {
  level: 0,
});

const emit = defineEmits<{
  select: [node: StandardSection | null];
  addChild: [parent: StandardSection];
  toggle: [nodeId: string];
}>()

const isExpanded = ref(props.expanded);

watch(
  () => props.expanded,
  (newVal) => {
    isExpanded.value = newVal;
  },
);

const isSelected = computed(() => props.selectedId === props.node.id);

const hasChildren = computed(
  () =>
    (props.node.children && props.node.children.length > 0) ||
    (props.node.requirements && props.node.requirements.length > 0),
);

// Professional calm color palette based on hierarchy level - sama seperti Assessment
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
  const level = props.level ?? 0;
  return colorPalette[level] ?? colorPalette[3]!;
});

// Icon: Sections always use Folder (regardless of having children)
// TreeNode is only used for sections, requirements are rendered inline
const nodeIcon = computed(() => {
  return isExpanded.value ? FolderOpen : Folder;
});

function toggleExpanded() {
  if (hasChildren.value) {
    isExpanded.value = !isExpanded.value;
    emit('toggle', props.node.id);
  }
}

function handleSelect() {
  // Toggle: klik item yang sama = unselect
  if (props.selectedId === props.node.id) {
    emit("select", null);
  } else {
    emit("select", props.node);
  }
}
</script>

<template>
  <div class="tree-node" :data-node-id="node.id">
    <!-- Node Content -->
    <div
      :class="
        cn(
          'flex items-center gap-1.5 px-2 py-1 select-none rounded-md cursor-pointer transition-all duration-150 text-[11px] group',
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

      <!-- Node Icon - sama seperti Assessment -->
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
          :class="`
            px-1.5 py-0 h-5 text-[10px] font-mono font-bold shrink-0 border
            ${isSelected
              ? 'text-white border-white/30 bg-white/20'
              : levelColors.badge
            }
          `"
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
              <div class="space-y-1">
                <p class="font-bold text-sm">{{ node.code }}</p>
                <p class="text-xs">{{ node.title }}</p>
              </div>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>

      <!-- Add Child Button (Visible on hover) -->
      <Button
        v-if="level < 3"
        variant="ghost"
        size="icon"
        class="h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity ml-auto"
        title="Add Child"
        @click.stop="emit('addChild', node)"
      >
        <Plus class="h-3.5 w-3.5 text-muted-foreground" />
      </Button>
    </div>

    <!-- Children -->
    <Transition name="expand">
      <div v-if="isExpanded && hasChildren" class="node-children">
        <TreeNode
          v-for="child in node.children"
          :key="child.id"
          :node="child"
          :selected-id="selectedId"
          :expanded="expandedNodes?.has(child.id) ?? false"
          :expanded-nodes="expandedNodes"
          :level="(level || 0) + 1"
          @select="emit('select', $event)"
          @toggle="emit('toggle', $event)"
          @add-child="emit('addChild', $event)"
        />

        <!-- Requirements (if any) - Styling serupa Assessment requirement -->
        <div
          v-for="req in node.requirements"
          :key="req.id"
          :data-node-id="req.id"
          :class="
            cn(
              'flex items-center gap-1.5 px-2 py-1 select-none rounded-md cursor-pointer transition-all duration-150 text-[11px] group',
              selectedId === req.id
                ? 'bg-amber-600 text-white font-medium shadow-md'
                : 'text-slate-700 hover:bg-amber-50/60',
            )
          "
          @click="
            () => {
              const reqNode = {
                ...req,
                type: 'requirement',
              } as unknown as StandardSection;
              // Toggle behavior
              if (props.selectedId === req.id) {
                emit('select', null);
              } else {
                emit('select', reqNode);
              }
            }
          "
        >
          <!-- Placeholder for alignment -->
          <div class="h-5 w-5 shrink-0" />
          
          <!-- Icon requirement - sama seperti Assessment -->
          <CheckCircle2
            v-if="selectedId === req.id"
            class="h-4 w-4 shrink-0 text-white"
          />
          <Circle
            v-else
            class="h-4 w-4 shrink-0 text-amber-600"
          />
          
          <TooltipProvider>
            <Tooltip :delay-duration="0">
              <TooltipTrigger as-child>
                <span class="truncate flex-1 text-[11px] font-medium">
                  {{ req.displayCode }} {{ req.title }}
                </span>
              </TooltipTrigger>
              <TooltipContent side="bottom" class="max-w-xs">
                <div class="space-y-1">
                  <p class="font-bold text-sm">{{ req.displayCode }}</p>
                  <p class="text-xs">{{ req.title }}</p>
                </div>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
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

/* Indentation guide line (tree connector) - sama seperti Assessment */
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
