<script setup lang="ts">
import { computed, watch, ref } from "vue";
import type { DateValue } from "@internationalized/date";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { DatePicker } from "@/components/ui/datepicker";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import type { CreateAssessmentRequest } from "../../types/assessment.types";
import { useStandards } from "../../composables/useAssessments";
import { Loader2 } from "lucide-vue-next";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { createAssessmentSchema } from "@/lib/validation/schemas/assessment";

const props = defineProps<{
  open: boolean;
  isSubmitting: boolean;
  isSuperAdmin?: boolean;
}>();

const emit = defineEmits<{
  (e: "update:open", value: boolean): void;
  (e: "submit", payload: CreateAssessmentRequest): void;
}>();

const { data: standardsData, isLoading: isLoadingStandards } = useStandards();
const standards = computed(() => standardsData.value?.data ?? []);

// Filter standards if needed, or just use all
const availableStandards = computed(() => standards.value);

// Date picker refs
const selectedStartDate = ref<DateValue>();
const selectedEndDate = ref<DateValue>();

// Typed schema for vee-validate
const validationSchema = toTypedSchema(createAssessmentSchema);

// Initialize form with vee-validate
const { handleSubmit, setValues, resetForm, errors, meta, defineField } = useForm({
  validationSchema,
  initialValues: {
    name: "",
    standardId: "",
    organizationId: "",
    periodValue: "",
    startDate: "",
    endDate: "",
  },
});

// Define fields using defineField
const [name, nameAttrs] = defineField('name');
const [standardId] = defineField('standardId');
const [periodValue, periodValueAttrs] = defineField('periodValue');

// Watch for dialog open to reset form
watch(
  () => props.open,
  (newValue) => {
    if (newValue) {
      resetForm();
      selectedStartDate.value = undefined;
      selectedEndDate.value = undefined;
    }
  },
);

// Sync date pickers with form values
watch(selectedStartDate, (newValue) => {
  setValues({ startDate: newValue ? newValue.toString() : "" });
});

watch(selectedEndDate, (newValue) => {
  setValues({ endDate: newValue ? newValue.toString() : "" });
});

// Form submission handler
const onSubmit = handleSubmit((formValues: any) => {
  const payload: CreateAssessmentRequest = {
    name: formValues.name.trim(),
    standardId: formValues.standardId,
    organizationId: formValues.organizationId || undefined,
    periodValue: formValues.periodValue || undefined,
    startDate: formValues.startDate || undefined,
    endDate: formValues.endDate || undefined,
  };
  emit("submit", payload);
});

const isValid = computed(() => meta.value.valid);
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>Create Assessment</DialogTitle>
        <DialogDescription>
          Create a new quality assessment for your organization.
        </DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="space-y-4 py-2">
        <!-- Assessment Name -->
        <div class="space-y-2">
          <Label for="name"
            >Assessment Name <span class="text-destructive">*</span></Label
          >
          <Input
            id="name"
            v-model="name"
            v-bind="nameAttrs"
            placeholder="e.g., Q1 2025 Self Assessment"
            :disabled="isSubmitting"
            :class="{ 'border-destructive': errors.name }"
          />
          <p v-if="errors.name" class="text-xs text-destructive">{{ errors.name }}</p>
        </div>

        <!-- Period Value -->
        <div class="space-y-2">
          <Label for="periodValue">Period Value</Label>
          <Input
            id="periodValue"
            v-model="periodValue"
            v-bind="periodValueAttrs"
            placeholder="e.g., April 2025, Q1 2025, Semester 1 2025"
            :disabled="isSubmitting"
            :class="{ 'border-destructive': errors.periodValue }"
          />
          <p v-if="errors.periodValue" class="text-xs text-destructive">{{ errors.periodValue }}</p>
        </div>

        <!-- Standard Selection -->
        <div class="space-y-2">
          <Label for="standard"
            >Standard <span class="text-destructive">*</span></Label
          >
          <Select
            v-model="standardId"
            :disabled="isSubmitting || isLoadingStandards"
          >
            <SelectTrigger :class="{ 'border-destructive': errors.standardId }">
              <SelectValue
                :placeholder="
                  isLoadingStandards
                    ? 'Loading standards...'
                    : 'Select a standard'
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="std in availableStandards"
                :key="std.id"
                :value="std.id"
              >
                {{ std.name }} ({{ std.version }})
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="errors.standardId" class="text-xs text-destructive">{{ errors.standardId }}</p>
        </div>

        <!-- Start & End Dates -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="startDate">Start Date</Label>
            <DatePicker
              v-model="selectedStartDate"
              placeholder="Select start date"
              :disabled="isSubmitting"
            />
          </div>
          <div class="space-y-2">
            <Label for="endDate">End Date</Label>
            <DatePicker
              v-model="selectedEndDate"
              placeholder="Select end date"
              :disabled="isSubmitting"
            />
          </div>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="emit('update:open', false)"
            :disabled="isSubmitting"
          >
            Cancel
          </Button>
          <Button type="submit" :disabled="!isValid || isSubmitting">
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            {{ isSubmitting ? "Creating..." : "Create Assessment" }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
