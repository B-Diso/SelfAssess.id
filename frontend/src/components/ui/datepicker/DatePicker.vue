<script setup lang="ts">
import { type DateValue } from "@internationalized/date";
import {
  DateFormatter,
  getLocalTimeZone,
  today,
} from "@internationalized/date";

import { CalendarIcon } from "lucide-vue-next";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";

defineProps<{
  placeholder?: string;
  disabled?: boolean;
}>();

const date = defineModel<DateValue>();
const defaultPlaceholder = today(getLocalTimeZone());

const df = new DateFormatter("id-ID", {
  dateStyle: "long",
});
</script>

<template>
  <Popover v-slot="{ close }">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="
          cn(
            'w-full justify-start text-left font-normal',
            !date && 'text-muted-foreground',
          )
        "
        :disabled="disabled"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{
          date
            ? df.format(date.toDate(getLocalTimeZone()))
            : placeholder || "Pick a date"
        }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" align="start">
      <Calendar
        v-model="date"
        :default-placeholder="defaultPlaceholder"
        layout="month-and-year"
        initial-focus
        @update:model-value="close"
      />
    </PopoverContent>
  </Popover>
</template>
