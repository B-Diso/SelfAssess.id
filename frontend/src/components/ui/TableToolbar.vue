<script setup lang="ts">
import { useVModel } from "@vueuse/core";
import { cn } from "@/lib/utils";
import { Search, SlidersHorizontal, X } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

interface Props {
  searchPlaceholder?: string;
  searchValue?: string;
  showSearch?: boolean;
  showReset?: boolean;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: "Search...",
  showSearch: true,
  showReset: false,
});

const emit = defineEmits<{
  (e: "update:searchValue", value: string): void;
  (e: "reset"): void;
}>();

const modelValue = useVModel(props, "searchValue", emit);
</script>

<template>
  <div
    :class="
      cn(
        'shrink-0 p-3 border-b bg-slate-50/50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between',
        $props.class,
      )
    "
  >
    <div class="flex flex-1 items-center gap-3">
      <!-- Search -->
      <div v-if="showSearch" class="relative flex-1 max-w-sm">
        <Search
          class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
        />
        <Input
          v-model="modelValue"
          type="text"
          :placeholder="searchPlaceholder"
          class="h-9 pl-9 bg-white border-slate-200 focus-visible:ring-primary/20 transition-all shadow-sm"
        />
        <Button
          v-if="modelValue"
          variant="ghost"
          size="icon"
          class="absolute right-1 top-1/2 -translate-y-1/2 h-7 w-7 text-muted-foreground hover:text-foreground"
          @click="modelValue = ''"
        >
          <X class="h-3.5 w-3.5" />
        </Button>
      </div>

      <!-- Filters Slot -->
      <div
        v-if="$slots.filters"
        class="flex flex-1 flex-wrap items-center gap-2"
      >
        <slot name="filters" />
      </div>

      <!-- Reset Button -->
      <Button
        v-if="showReset"
        variant="ghost"
        size="sm"
        class="h-9 text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-slate-200/50 transition-colors"
        @click="$emit('reset')"
      >
        <SlidersHorizontal class="h-3.5 w-3.5 mr-2" />
        Reset
      </Button>
    </div>

    <!-- Right side slot for extra actions if needed -->
    <div v-if="$slots.actions" class="flex items-center gap-2 ml-auto">
      <slot name="actions" />
    </div>
  </div>
</template>
