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
import type { StandardSection } from "../../types/standard";
import TiptapEditor from "@/components/ui/tiptap/TiptapEditor.vue";
import { useZodForm } from "@/lib/validation";
import { sectionSchema, type SectionForm } from "@/lib/validation/schemas/standard";

interface Props {
  open: boolean;
  section: StandardSection | null;
  parentId?: string | null;
  isSubmitting?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isSubmitting: false,
  parentId: null,
});

const emit = defineEmits<{
  (e: "update:open", value: boolean): void;
  (e: "submit", data: SectionForm): void;
}>();

const isEditing = computed(() => !!props.section);

const { handleSubmit, resetForm, setValues, errors, meta, defineField } = useZodForm(
  sectionSchema,
  {
    initialValues: {
      title: "",
      code: "",
      type: "domain",
      description: "",
    },
  }
);

// Define fields using vee-validate's defineField
const [title, titleAttrs] = defineField('title');
const [code, codeAttrs] = defineField('code');
const [description, descriptionAttrs] = defineField('description');

const onSubmit = handleSubmit(async (values: SectionForm) => {
  emit("submit", {
    title: values.title,
    code: values.code,
    type: values.type,
    description: values.description,
  });
});

watch(
  () => props.open,
  async (open, oldOpen) => {
    if (open && !oldOpen) {
      await nextTick();
      if (props.section) {
        setValues({
          title: props.section.title ?? "",
          code: props.section.code ?? "",
          type: props.section.type ?? "domain",
          description: props.section.description ?? "",
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
          isEditing ? "Edit Section" : "Create Section"
        }}</DialogTitle>
        <DialogDescription>
          {{
            isEditing
              ? "Update the details of this standard section."
              : "Add a new section to this standard."
          }}
        </DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="grid gap-4 py-4">
        <!-- Title -->
        <div class="grid gap-2">
          <Label for="title">Title <span class="text-red-500">*</span></Label>
          <Input
            id="title"
            v-model="title"
            v-bind="titleAttrs"
            placeholder="e.g., Governance and Risk Management"
            :class="{ 'border-destructive': errors.title }"
          />
          <p v-if="errors.title" class="text-xs text-destructive">
            {{ errors.title }}
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Code -->
          <div class="grid gap-2">
            <Label for="code">Code <span class="text-red-500">*</span></Label>
            <Input
              id="code"
              v-model="code"
              v-bind="codeAttrs"
              placeholder="e.g., GOV"
              :class="{ 'border-destructive': errors.code }"
            />
            <p v-if="errors.code" class="text-xs text-destructive">
              {{ errors.code }}
            </p>
          </div>
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
              placeholder="Detailed description of this section"
            />
          </div>
          <p v-if="errors.description" class="text-xs text-destructive">
            {{ errors.description }}
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
              isEditing ? "Update Section" : "Create Section"
            }}</template>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
