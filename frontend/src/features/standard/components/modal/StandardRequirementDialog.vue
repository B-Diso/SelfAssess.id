<script setup lang="ts">
import { watch, nextTick, computed } from "vue";
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
import type { StandardRequirement } from "../../types/standard";
import TiptapEditor from "@/components/ui/tiptap/TiptapEditor.vue";
import { useZodForm } from "@/lib/validation";
import { requirementSchema, type RequirementForm } from "@/lib/validation/schemas/standard";

interface Props {
  open: boolean;
  requirement: StandardRequirement | null;
  isSubmitting?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isSubmitting: false,
});

const emit = defineEmits<{
  (e: "update:open", value: boolean): void;
  (e: "submit", data: RequirementForm): void;
}>();

const isEditing = computed(() => !!props.requirement);

const { handleSubmit, resetForm, setValues, errors, meta, defineField } = useZodForm(
  requirementSchema,
  {
    initialValues: {
      title: "",
      displayCode: "",
      description: "",
      evidenceHint: "",
    },
  }
);

// Define fields using vee-validate's defineField
const [title, titleAttrs] = defineField('title');
const [displayCode, displayCodeAttrs] = defineField('displayCode');
const [description, descriptionAttrs] = defineField('description');
const [evidenceHint, evidenceHintAttrs] = defineField('evidenceHint');

const onSubmit = handleSubmit(async (values: RequirementForm) => {
  emit("submit", {
    title: values.title,
    displayCode: values.displayCode,
    description: values.description,
    evidenceHint: values.evidenceHint,
  });
});

watch(
  () => props.open,
  async (open, oldOpen) => {
    if (open && !oldOpen) {
      await nextTick();
      if (props.requirement) {
        setValues({
          title: props.requirement.title ?? "",
          displayCode: props.requirement.displayCode ?? "",
          description: props.requirement.description ?? "",
          evidenceHint: props.requirement.evidenceHint ?? "",
        });
      } else {
        resetForm();
      }
    }
    if (!open) {
      resetForm();
    }
  }
);

function closeDialog() {
  emit("update:open", false);
}
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{
          isEditing ? "Edit Requirement" : "Create Requirement"
        }}</DialogTitle>
        <DialogDescription>
          {{
            isEditing
              ? "Update the details of this standard requirement."
              : "Add a new requirement to this section."
          }}
        </DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="grid gap-4 py-4">
        <!-- Display Code -->
        <div class="grid gap-2">
          <Label for="displayCode">Requirement Code <span class="text-red-500">*</span></Label>
          <Input
            id="displayCode"
            v-model="displayCode"
            v-bind="displayCodeAttrs"
            placeholder="e.g., GOV.1.1"
            :class="{ 'border-destructive': errors.displayCode }"
          />
          <p v-if="errors.displayCode" class="text-xs text-destructive">
            {{ errors.displayCode }}
          </p>
        </div>

        <!-- Title -->
        <div class="grid gap-2">
          <Label for="title">Title <span class="text-red-500">*</span></Label>
          <Input
            id="title"
            v-model="title"
            v-bind="titleAttrs"
            placeholder="e.g., Board Oversight of Risk Management"
            :class="{ 'border-destructive': errors.title }"
          />
          <p v-if="errors.title" class="text-xs text-destructive">
            {{ errors.title }}
          </p>
        </div>

        <!-- Description -->
        <div class="grid gap-2">
          <Label for="description">Description (Optional)</Label>
          <div
            class="rounded-lg border shadow-sm overflow-hidden bg-card"
            :class="{ 'border-destructive': errors.description }"
          >
            <TiptapEditor
              id="description"
              v-model="description"
              v-bind="descriptionAttrs"
              placeholder="Detailed description of the requirement"
              :rows="3"
            />
          </div>
          <p v-if="errors.description" class="text-xs text-destructive">
            {{ errors.description }}
          </p>
        </div>

        <!-- Evidence Hint -->
        <div class="grid gap-2">
          <Label for="evidenceHint">Evidence Hint (Optional)</Label>
          <div
            class="rounded-lg border shadow-sm overflow-hidden bg-card"
            :class="{ 'border-destructive': errors.evidenceHint }"
          >
            <TiptapEditor
              id="evidenceHint"
              v-model="evidenceHint"
              v-bind="evidenceHintAttrs"
              placeholder="Tips on what evidence might satisfy this requirement"
              :rows="2"
            />
          </div>
          <p v-if="errors.evidenceHint" class="text-xs text-destructive">
            {{ errors.evidenceHint }}
          </p>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            type="button"
            :disabled="isSubmitting"
            @click="closeDialog"
          >
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting || !meta.valid">
            <template v-if="isSubmitting">Saving...</template>
            <template v-else>{{
              isEditing ? "Update Requirement" : "Create Requirement"
            }}</template>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
