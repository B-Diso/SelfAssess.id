<script setup lang="ts">
import type { AssessmentTreeNode } from "../../types/assessment.types";
import { computed } from "vue";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import {
  Layers,
  FileText,
  CheckCircle2,
  ListTodo,
  ArrowRight,
} from "lucide-vue-next";

interface Props {
  node: AssessmentTreeNode;
}

const props = defineProps<Props>();

const requirements = computed(
  () => props.node.children?.filter((n) => n.type === "requirement") || [],
);

const sections = computed(
  () => props.node.children?.filter((n) => n.type !== "requirement") || [],
);

const hasRequirements = computed(() => requirements.value.length > 0);

const hasSections = computed(() => sections.value.length > 0);

const requirementsCount = computed(() => requirements.value.length);

const completedRequirements = computed(
  () =>
    requirements.value.filter((req) => req.requirement?.status === "reviewed")
      .length,
);

const completionPercentage = computed(() => {
  if (requirementsCount.value === 0) return 0;
  return Math.round(
    (completedRequirements.value / requirementsCount.value) * 100,
  );
});
</script>

<template>
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <!-- Header Card -->
    <Card class="border-l-4 border-l-primary overflow-hidden">
      <CardHeader class="pb-6">
        <div class="flex items-start justify-between gap-4">
          <div class="space-y-4 flex-1">
            <div class="flex items-center gap-2">
              <Badge
                variant="outline"
                class="px-2 py-0.5 h-5 text-[10px] font-mono font-bold bg-muted/50 text-muted-foreground border-muted-foreground/20"
              >
                {{ node.code }}
              </Badge>
              <Badge
                v-if="node.type === 'domain'"
                class="px-2 py-0.5 h-5 text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-300"
              >
                Domain
              </Badge>
              <Badge
                v-else-if="node.type === 'element'"
                class="px-2 py-0.5 h-5 text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary"
              >
                Principle
              </Badge>
              <Badge
                v-else
                class="px-2 py-0.5 h-5 text-[10px] font-bold uppercase tracking-wider bg-muted text-muted-foreground"
              >
                {{ node.type }}
              </Badge>
            </div>
            <div class="space-y-2">
              <CardTitle class="text-2xl font-extrabold tracking-tight">{{
                node.title
              }}</CardTitle>
              <CardDescription class="text-[13px] leading-relaxed max-w-3xl">
                {{ node.description }}
              </CardDescription>
            </div>
          </div>
          <div class="hidden sm:flex p-3 bg-primary/5 rounded-xl">
            <Layers class="w-8 h-8 text-primary/40" />
          </div>
        </div>
      </CardHeader>
    </Card>

    <!-- Requirements Under This Section -->
    <Card class="flex flex-col overflow-hidden">
      <CardHeader class="pb-4 border-b">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-primary/10 rounded-lg">
              <ListTodo class="w-5 h-5 text-primary" />
            </div>
            <div>
              <CardTitle class="text-sm font-bold uppercase tracking-tight">
                {{ hasRequirements ? "Sub-Requirements" : "Sub-Sections" }}
              </CardTitle>
              <CardDescription class="text-[10px]">
                {{ requirementsCount }} total items in this section
              </CardDescription>
            </div>
          </div>
          <Badge
            v-if="hasRequirements"
            variant="secondary"
            class="text-[10px] font-bold h-6"
          >
            {{ completedRequirements }} / {{ requirementsCount }} Completed
          </Badge>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <!-- Show Requirements if available -->
        <div v-if="hasRequirements" class="divide-y divide-border/50">
          <div
            v-for="req in requirements"
            :key="req.id"
            class="group flex items-center justify-between p-4 hover:bg-muted/30 transition-all cursor-pointer"
          >
            <div class="flex items-start gap-3 flex-1 min-w-0">
              <div
                class="mt-0.5 p-1.5 rounded-md transition-colors"
                :class="
                  req.requirement?.status === 'reviewed'
                    ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'
                    : 'bg-muted text-muted-foreground'
                "
              >
                <component
                  :is="
                    req.requirement?.status === 'reviewed'
                      ? CheckCircle2
                      : FileText
                  "
                  class="w-3.5 h-3.5"
                />
              </div>
              <div class="flex-1 min-w-0 space-y-1">
                <div class="flex items-center gap-2">
                  <span class="font-mono text-[10px] font-bold text-primary">{{
                    req.code
                  }}</span>
                  <Badge
                    v-if="req.requirement?.status === 'reviewed'"
                    class="h-4 px-1 text-[8px] font-black uppercase bg-green-500 hover:bg-green-500"
                  >
                    Done
                  </Badge>
                </div>
                <p
                  class="text-sm font-bold truncate group-hover:text-primary transition-colors"
                >
                  {{ req.title }}
                </p>
              </div>
            </div>
            <ArrowRight
              class="w-4 h-4 text-muted-foreground/30 group-hover:text-primary group-hover:translate-x-1 transition-all"
            />
          </div>

          <!-- Progress Bar Section -->
          <div class="p-6 bg-muted/20 border-t">
            <div class="space-y-3 max-w-md">
              <div
                class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-muted-foreground"
              >
                <span>Overall Completion</span>
                <span>{{ completionPercentage }}%</span>
              </div>
              <Progress :value="completionPercentage" class="h-1.5" />
            </div>
          </div>
        </div>

        <!-- Show Sections if no requirements -->
        <div v-else-if="hasSections" class="divide-y divide-border/50">
          <div
            v-for="section in sections"
            :key="section.id"
            class="group flex items-center justify-between p-4 hover:bg-muted/30 transition-all cursor-pointer"
          >
            <div class="flex items-start gap-4 flex-1 min-w-0">
              <div
                class="mt-1 p-2 bg-muted rounded-lg group-hover:bg-primary/10 transition-colors"
              >
                <Layers
                  class="w-4 h-4 text-muted-foreground group-hover:text-primary"
                />
              </div>
              <div class="flex-1 min-w-0 space-y-1">
                <span
                  class="font-mono text-[10px] font-bold text-muted-foreground"
                  >{{ section.code }}</span
                >
                <p
                  class="text-sm font-bold group-hover:text-primary transition-colors"
                >
                  {{ section.title }}
                </p>
              </div>
            </div>
            <ArrowRight
              class="w-4 h-4 text-muted-foreground/30 group-hover:text-primary group-hover:translate-x-1 transition-all"
            />
          </div>
        </div>

        <!-- No Requirements or Sections -->
        <div
          v-else
          class="flex flex-col items-center justify-center py-12 text-center"
        >
          <div class="p-3 bg-muted/50 rounded-full mb-3">
            <Layers class="w-8 h-8 opacity-20" />
          </div>
          <p class="text-xs font-bold text-muted-foreground">
            No content found
          </p>
          <p class="text-[10px] text-muted-foreground/60 mt-1">
            There are no sub-items under this section.
          </p>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
