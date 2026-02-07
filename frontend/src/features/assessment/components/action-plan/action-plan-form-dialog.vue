<script setup lang="ts">
import { ref, watch, computed } from "vue";
import type { DateValue } from "@internationalized/date";
import { parseDate } from "@internationalized/date";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { DatePicker } from "@/components/ui/datepicker";
import { ScrollArea } from "@/components/ui/scroll-area";
import type { ActionPlan } from "../../types/assessment.types";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { actionPlanSchema, type ActionPlanForm } from "@/lib/validation/schemas/assessment";

interface Props {
  open: boolean;
  actionPlan?: ActionPlan | null;
  requirementId?: string;
  isSubmitting?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isSubmitting: false,
});

const emit = defineEmits<{
  "update:open": [value: boolean];
  submit: [data: ActionPlanForm];
}>();

// Date picker ref
const selectedDate = ref<DateValue>();

// Track edit mode
const isEditMode = computed(() => !!props.actionPlan);

// Typed schema for vee-validate
const validationSchema = toTypedSchema(actionPlanSchema);

// Initialize form with vee-validate
const { handleSubmit, setValues, resetForm, errors, meta, defineField } = useForm({
  validationSchema,
  initialValues: {
    title: "",
    actionPlan: "",
    pic: "",
    dueDate: "",
  },
});

// Define fields using defineField
const [title, titleAttrs] = defineField('title');
const [actionPlanField, actionPlanAttrs] = defineField('actionPlan');
const [pic, picAttrs] = defineField('pic');

// Watch for dialog open to populate or reset form
watch(
  () => props.open,
  (newValue) => {
    if (newValue) {
      if (props.actionPlan) {
        // Edit mode - populate form
        setValues({
          title: props.actionPlan.title,
          actionPlan: props.actionPlan.actionPlan || "",
          pic: props.actionPlan.pic,
          dueDate: props.actionPlan.dueDate,
        });
        // Set date picker value
        if (props.actionPlan.dueDate) {
          try {
            selectedDate.value = parseDate(
              props.actionPlan.dueDate.split("T")[0] ?? "",
            );
          } catch {
            selectedDate.value = undefined;
          }
        }
      } else {
        // Create mode - reset form
        resetForm();
        selectedDate.value = undefined;
      }
    }
  },
);

// Sync date picker with form dueDate
watch(selectedDate, (newValue) => {
  setValues({ dueDate: newValue ? newValue.toString() : "" });
});

// Form submission handler
const onSubmit = handleSubmit((formValues) => {
  emit("submit", formValues);
});

function handleOpenChange(value: boolean) {
  emit("update:open", value);
}

const isValid = computed(() => meta.value.valid);
</script>

<template>
  <Dialog :open="open" @update:open="handleOpenChange">
    <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-xl font-bold">{{
          isEditMode ? "Edit Action Plan" : "Create Action Plan"
        }}</DialogTitle>
        <DialogDescription class="text-xs">
          {{
            isEditMode
              ? "Update the corrective action plan details."
              : "Add a new corrective action plan for this requirement."
          }}
        </DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit">
        <ScrollArea class="max-h-[60vh]">
          <div class="px-6 py-4 space-y-3">
            <div class="space-y-1.5">
              <Label
                for="title"
                class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                >Title <span class="text-destructive">*</span></Label
              >
              <Input
                id="title"
                v-model="title"
                v-bind="titleAttrs"
                placeholder="Action plan title..."
                :disabled="isSubmitting"
                :class="['h-9', { 'border-destructive': errors.title }]"
              />
              <p v-if="errors.title" class="text-xs text-destructive">{{ errors.title }}</p>
            </div>

            <div class="space-y-1.5">
              <Label
                for="actionPlan"
                class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                >Description</Label
              >
              <Textarea
                id="actionPlan"
                v-model="actionPlanField"
                v-bind="actionPlanAttrs"
                placeholder="Describe the action in detail..."
                :disabled="isSubmitting"
                :rows="3"
                :class="['resize-none text-sm', { 'border-destructive': errors.actionPlan }]"
              />
              <p v-if="errors.actionPlan" class="text-xs text-destructive">{{ errors.actionPlan }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <Label
                  for="pic"
                  class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                  >PIC <span class="text-destructive">*</span></Label
                >
                <Input
                  id="pic"
                  v-model="pic"
                  v-bind="picAttrs"
                  placeholder="PIC name"
                  :disabled="isSubmitting"
                  :class="['h-9', { 'border-destructive': errors.pic }]"
                />
                <p v-if="errors.pic" class="text-xs text-destructive">{{ errors.pic }}</p>
              </div>

              <div class="space-y-1.5">
                <Label
                  for="dueDate"
                  class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                  >Due Date <span class="text-destructive">*</span></Label
                >
                <DatePicker
                  v-model="selectedDate"
                  placeholder="Select date"
                  :disabled="isSubmitting"
                  :class="{ 'border-destructive': errors.dueDate }"
                />
                <p v-if="errors.dueDate" class="text-xs text-destructive">{{ errors.dueDate }}</p>
              </div>
            </div>
          </div>
        </ScrollArea>

        <DialogFooter class="bg-muted/30 px-6 py-3 border-t">
          <Button
            type="button"
            variant="ghost"
            @click="handleOpenChange(false)"
            :disabled="isSubmitting"
            class="h-9 text-xs"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="!isValid || isSubmitting"
            class="h-9 text-xs px-6"
          >
            {{ isEditMode ? "Save Changes" : "Create Plan" }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
