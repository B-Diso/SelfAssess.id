<script setup lang="ts">
import { ref, computed, watch, nextTick } from "vue";
import {
  Expand,
  Shrink,
  Search,
  FolderOpen,
  Filter,
  X,
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from "@/components/ui/dropdown-menu";
import AssessmentTreeNode from "./AssessmentTreeNode.vue";
import type { AssessmentTreeNode as TreeNodeType } from "../../types/assessment.types";
import type { AssessmentResponseStatus } from "../../types/assessment-response.types";

interface Props {
  nodes: TreeNodeType[];
  selectedId?: string;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<{
  select: [nodeId: string];
}>();

const expandedNodes = ref<Set<string>>(new Set());
const searchQuery = ref("");
const statusFilter = ref<AssessmentResponseStatus | "all">("all");

// Auto-expand first two levels to show requirements
watch(
  () => props.nodes,
  (nodes) => {
    if (nodes.length > 0 && expandedNodes.value.size === 0) {
      const toExpand = new Set<string>();
      nodes.forEach((node) => {
        toExpand.add(node.id);
        node.children?.forEach((child) => {
          toExpand.add(child.id);
        });
      });
      expandedNodes.value = toExpand;
    }
  },
  { immediate: true },
);

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

const hasNodes = computed(() => props.nodes && props.nodes.length > 0);

// Calculate progress stats
const stats = computed(() => {
  let total = 0;
  let reviewed = 0;
  let pendingReview = 0;

  function countNodes(nodes: TreeNodeType[]) {
    for (const node of nodes) {
      if (node.type === "requirement") {
        total++;
        if (node.requirement?.status === "reviewed") {
          reviewed++;
        } else if (node.requirement?.status === "pending_review") {
          pendingReview++;
        }
      }
      if (node.children?.length) {
        countNodes(node.children);
      }
    }
  }

  countNodes(props.nodes);
  return { total, reviewed, pendingReview };
});

// Progress percentage
// Calculation: reviewed = 100%, pending_review = 50%, active = 0%
const progressPercent = computed(() => {
  if (stats.value.total === 0) return 0;
  const totalScore = (stats.value.reviewed * 100) + (stats.value.pendingReview * 50);
  return Math.round(totalScore / stats.value.total);
});

const filteredNodes = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  const filter = statusFilter.value;

  function filterNode(node: TreeNodeType): TreeNodeType | null {
    const matchesTitle = query && node.title.toLowerCase().includes(query);
    const matchesCode = query && node.code.toLowerCase().includes(query);
    const matchesDescription = query && node.description?.toLowerCase().includes(query);
    const matchesSearch = !query || matchesTitle || matchesCode || matchesDescription;

    // For requirement nodes, also check status filter
    let matchesStatusFilter = true;
    if (node.type === "requirement" && filter !== "all") {
      const responseStatus = node.requirement?.status;
      matchesStatusFilter = responseStatus === filter;
    }

    // Recursively filter children
    const matchingChildren = node.children
      ?.map((child) => filterNode(child))
      .filter((child): child is TreeNodeType => child !== null);

    // Node matches if:
    // 1. It matches search criteria
    // 2. For requirements: matches status filter OR has matching children
    // 3. For non-requirements: has matching children
    const hasMatchingChildren = matchingChildren && matchingChildren.length > 0;

    if (node.type === "requirement") {
      if (matchesSearch && matchesStatusFilter) {
        return { ...node, children: matchingChildren };
      }
    } else {
      // For section/domain/element nodes, include if has matching children
      if (hasMatchingChildren || (matchesSearch && !query)) {
        return { ...node, children: matchingChildren || node.children };
      }
    }

    return null;
  }

  return props.nodes
    .map((node) => filterNode(node))
    .filter((node): node is TreeNodeType => node !== null);
});

function expandAll() {
  const allIds = getAllNodeIds(props.nodes);
  expandedNodes.value = new Set(allIds);
}

function collapseAll() {
  expandedNodes.value = new Set();
}

function clearStatusFilter() {
  statusFilter.value = "all";
}

function getAllNodeIds(nodes: TreeNodeType[]): string[] {
  const ids: string[] = [];
  for (const node of nodes) {
    ids.push(node.id);
    if (node.children && node.children.length > 0) {
      ids.push(...getAllNodeIds(node.children));
    }
  }
  return ids;
}

function handleSelect(nodeId: string) {
  // Toggle selection: klik pertama select, klik kedua unselect
  if (props.selectedId === nodeId) {
    emit("select", "");
  } else {
    emit("select", nodeId);
  }
}
</script>

<template>
  <div class="flex flex-col h-full bg-background border-r">
    <!-- Header -->
    <div class="p-3 border-b space-y-2">
      <!-- Row 1: Title + Progress Stats -->
      <div class="flex items-center justify-between gap-2">
        <div class="min-w-0 flex-1">
          <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground truncate">
            Assessment Tree
          </h2>
          <div class="text-[10px] text-muted-foreground mt-0.5">
            <span class="font-medium text-green-600">{{ stats.reviewed }}</span>
            <span>/</span>
            <span>{{ stats.total }}</span>
            <span class="text-muted-foreground/70">reviewed</span>
            <span v-if="stats.pendingReview > 0" class="ml-1.5 text-amber-600">
              ({{ stats.pendingReview }} pending)
            </span>
          </div>
        </div>
        
        <!-- Progress Badge -->
        <div 
          class="text-[10px] font-medium px-1.5 py-0.5 rounded shrink-0"
          :class="{
            'bg-green-100 text-green-700': progressPercent === 100,
            'bg-amber-100 text-amber-700': progressPercent >= 50 && progressPercent < 100,
            'bg-slate-100 text-slate-600': progressPercent < 50
          }"
        >
          {{ progressPercent }}%
        </div>
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
            placeholder="Filter requirements..."
            class="pl-8 h-7 text-xs"
          />
        </div>

        <!-- Status Filter Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="ghost"
              size="icon"
              class="h-7 w-7 relative"
              :class="statusFilter !== 'all' && 'bg-primary/10 text-primary'"
              title="Filter by Response Status"
            >
              <Filter class="h-3.5 w-3.5" />
              <!-- Active indicator dot -->
              <span
                v-if="statusFilter !== 'all'"
                class="absolute top-1 right-1 h-1.5 w-1.5 rounded-full bg-primary"
              />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuLabel>Filter by Status</DropdownMenuLabel>
            <DropdownMenuSeparator />

            <!-- All Requirements -->
            <DropdownMenuItem
              @click="statusFilter = 'all'"
              :class="{ 'bg-accent text-accent-foreground': statusFilter === 'all' }"
            >
              <div class="flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-slate-400" />
                <span class="text-xs flex-1">All Requirements</span>
                <CheckCircle2
                  v-if="statusFilter === 'all'"
                  class="h-3.5 w-3.5 text-slate-600 shrink-0"
                />
              </div>
            </DropdownMenuItem>

            <DropdownMenuSeparator />

            <!-- Active -->
            <DropdownMenuItem
              @click="statusFilter = 'active'"
              :class="{ 'bg-accent text-accent-foreground': statusFilter === 'active' }"
            >
              <div class="flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-slate-500" />
                <span class="text-xs flex-1">Active</span>
                <CheckCircle2
                  v-if="statusFilter === 'active'"
                  class="h-3.5 w-3.5 text-slate-600 shrink-0"
                />
              </div>
            </DropdownMenuItem>

            <!-- Pending Review -->
            <DropdownMenuItem
              @click="statusFilter = 'pending_review'"
              :class="{ 'bg-accent text-accent-foreground text-blue-500': statusFilter === 'pending_review' }"
            >
              <div class="flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-blue-500" />
                <span class="text-xs flex-1">Pending Review</span>
                <CheckCircle2
                  v-if="statusFilter === 'pending_review'"
                  class="h-3.5 w-3.5 shrink-0"
                />
              </div>
            </DropdownMenuItem>

            <!-- Reviewed -->
            <DropdownMenuItem
              @click="statusFilter = 'reviewed'"
              :class="{ 'bg-accent text-accent-foreground text-green-500': statusFilter === 'reviewed' }"
            >
              <div class="flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-green-500" />
                <span class="text-xs flex-1">Reviewed</span>
                <CheckCircle2
                  v-if="statusFilter === 'reviewed'"
                  class="h-3.5 w-3.5 shrink-0"
                />
              </div>
            </DropdownMenuItem>

            <!-- Clear Filter Action -->
            <DropdownMenuSeparator />
            <DropdownMenuItem
              v-if="statusFilter !== 'all'"
              @click="clearStatusFilter"
            >
              <div class="flex items-center justify-center gap-1.5">
                <X class="h-3 w-3" />
                <span class="text-xs">Clear Filter</span>
              </div>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

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
        <div v-else-if="!hasNodes" class="text-center py-8">
          <FolderOpen class="h-10 w-10 mx-auto text-muted-foreground/40 mb-3" />
          <p class="text-xs text-muted-foreground">No requirements available</p>
        </div>

        <!-- Tree Nodes -->
        <div v-else-if="filteredNodes.length > 0" class="space-y-0.5">
          <AssessmentTreeNode
            v-for="node in filteredNodes"
            :key="node.id"
            :node="node"
            :selected-id="selectedId"
            :expanded="expandedNodes.has(node.id)"
            :expanded-nodes="expandedNodes"
            @select="handleSelect"
            @toggle="
              (id) => {
                if (expandedNodes.has(id)) {
                  expandedNodes.delete(id);
                } else {
                  expandedNodes.add(id);
                }
              }
            "
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
