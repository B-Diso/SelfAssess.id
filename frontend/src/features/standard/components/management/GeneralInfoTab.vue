<script setup lang="ts">
import { ref, watch } from "vue";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import TiptapEditor from "@/components/ui/tiptap/TiptapEditor.vue";
import type { StandardRequirement } from "../../types/standard";

interface Props {
  requirement?: StandardRequirement | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  update: [data: Partial<StandardRequirement>];
}>();

const form = ref({
  displayCode: props.requirement?.displayCode || "",
  title: props.requirement?.title || "",
  description: props.requirement?.description || "",
  evidenceHint: props.requirement?.evidenceHint || "",
});

// Update form when requirement changes
watch(
  () => props.requirement,
  (newReq) => {
    if (newReq) {
      form.value = {
        displayCode: newReq.displayCode || "",
        title: newReq.title || "",
        description: newReq.description || "",
        evidenceHint: newReq.evidenceHint || "",
      };
    }
  },
  { immediate: true },
);

// Emit changes
watch(
  form,
  (newForm) => {
    emit("update", newForm);
  },
  { deep: true },
);
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-6">
      <div class="space-y-2">
        <Label for="display-code">Display Code</Label>
        <Input
          id="display-code"
          v-model="form.displayCode"
          placeholder="e.g., REQ-1.1"
        />
      </div>

      <div class="space-y-2">
        <Label for="title">Title</Label>
        <Input
          id="title"
          v-model="form.title"
          placeholder="Enter requirement title"
        />
      </div>

      <div class="space-y-2">
        <Label for="description">Description</Label>
        <div
            class="rounded-lg border shadow-sm overflow-hidden bg-card"
          >
            <TiptapEditor
              id="description"
              v-model="form.description"
              placeholder="Enter requirement description"
              :rows="4"
            />
          </div>
      </div>

      <div class="space-y-2">
        <Label for="evidence-hint">Evidence Hint</Label>
        <div
            class="rounded-lg border shadow-sm overflow-hidden bg-card"
          >
            <TiptapEditor
              id="evidence-hint"
              v-model="form.evidenceHint"
              placeholder="Provide guidance on what evidence is needed"
              :rows="3"
            />
          </div>
      </div>
    </div>
  </div>
</template>
