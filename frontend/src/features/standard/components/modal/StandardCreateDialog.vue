<script setup lang="ts">
import { watch } from "vue";
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
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useZodForm } from "@/lib/validation";
import { createStandardSchema, type CreateStandardForm } from "@/lib/validation/schemas/standard";
import type { StandardType } from "../../types/standard";

interface Props {
  open: boolean;
  isSubmitting?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isSubmitting: false,
});

const emit = defineEmits<{
  (e: "update:open", value: boolean): void;
  (e: "submit", data: CreateStandardForm): void;
}>();

const { handleSubmit, resetForm, errors, defineField } = useZodForm(
  createStandardSchema,
  {
    initialValues: {
      name: "",
      version: "",
      type: "internal" as StandardType,
      description: "",
      periodType: "annual" as const,
    },
  }
);

// Define fields using vee-validate's defineField
const [name, nameAttrs] = defineField('name');
const [version, versionAttrs] = defineField('version');
const [type, typeAttrs] = defineField('type');
const [description, descriptionAttrs] = defineField('description');
const [periodType, periodTypeAttrs] = defineField('periodType');

const onSubmit = handleSubmit(async (values: CreateStandardForm) => {
  emit("submit", {
    name: values.name.trim(),
    version: values.version.trim(),
    type: values.type,
    description: values.description?.trim() || undefined,
    periodType: values.periodType || undefined,
  });
});

watch(
  () => props.open,
  (newVal) => {
    if (!newVal) {
      resetForm();
    }
  }
);

function handleOpenChange(value: boolean) {
  emit("update:open", value);
}

function closeDialog() {
  emit("update:open", false);
}
</script>

<template>
  <Dialog :open="open" @update:open="handleOpenChange">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>Create New Standard</DialogTitle>
        <DialogDescription>
          Create a new assessment standard template. Fill in the details below.
        </DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="name">Name <span class="text-red-500">*</span></Label>
          <Input
            id="name"
            v-model="name"
            v-bind="nameAttrs"
            placeholder="e.g., Global Internal Audit Standards 2024"
            :disabled="isSubmitting"
            :class="{ 'border-destructive': errors.name }"
          />
          <p v-if="errors.name" class="text-sm text-destructive">
            {{ errors.name }}
          </p>
        </div>

        <div class="grid gap-2">
          <Label for="version">
            Version <span class="text-red-500">*</span>
          </Label>
          <Input
            id="version"
            v-model="version"
            v-bind="versionAttrs"
            placeholder="e.g., 2024.0"
            :disabled="isSubmitting"
            :class="{ 'border-destructive': errors.version }"
          />
          <p v-if="errors.version" class="text-sm text-destructive">
            {{ errors.version }}
          </p>
        </div>

        <div class="grid gap-2">
          <Label for="type">Type</Label>
          <Select
            id="type"
            v-model="type"
            v-bind="typeAttrs"
            :disabled="isSubmitting"
          >
            <SelectTrigger :class="{ 'border-destructive': errors.type }">
              <SelectValue placeholder="Select type" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="internal">Internal</SelectItem>
                <SelectItem value="regulatory">Regulatory</SelectItem>
                <SelectItem value="standard">Standard</SelectItem>
                <SelectItem value="bestPractice">Best Practice</SelectItem>
                <SelectItem value="other">Other</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <p v-if="errors.type" class="text-sm text-destructive">
            {{ errors.type }}
          </p>
        </div>

        <div class="grid gap-2">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="description"
            v-bind="descriptionAttrs"
            placeholder="Enter a description for this standard..."
            :disabled="isSubmitting"
            :rows="4"
            :class="{ 'border-destructive': errors.description }"
          />
          <p v-if="errors.description" class="text-sm text-destructive">
            {{ errors.description }}
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-2">
            <Label for="period-type">Period Type</Label>
            <Select
              id="period-type"
              v-model="periodType"
              v-bind="periodTypeAttrs"
              :disabled="isSubmitting"
            >
              <SelectTrigger :class="{ 'border-destructive': errors.periodType }">
                <SelectValue placeholder="Select period" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="annual">Annual</SelectItem>
                  <SelectItem value="semester">Semesterly</SelectItem>
                  <SelectItem value="quarterly">Quarterly</SelectItem>
                  <SelectItem value="monthly">Monthly</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <p v-if="errors.periodType" class="text-sm text-destructive">
              {{ errors.periodType }}
            </p>
          </div>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            :disabled="isSubmitting"
            @click="closeDialog"
          >
            Cancel
          </Button>
          <Button type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? "Creating..." : "Create Standard" }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
