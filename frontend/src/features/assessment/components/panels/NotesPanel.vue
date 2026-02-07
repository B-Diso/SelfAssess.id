<script setup lang="ts">
import { ref, watch } from "vue";
import type {
  AssessmentResponse,
  UpdateNotesRequest,
} from "../../types/assessment.types";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Save, StickyNote, FileText } from "lucide-vue-next";

const props = defineProps<{
  response: AssessmentResponse | null;
}>();

const emit = defineEmits<{
  save: [data: UpdateNotesRequest];
}>();

// Form state
const comments = ref("");
const hasChanges = ref(false);
const isSaving = ref(false);

// Initialize form data when response changes
watch(
  () => props.response,
  (newResponse) => {
    if (newResponse) {
      comments.value = newResponse.comments || "";
      hasChanges.value = false;
    }
  },
  { immediate: true },
);

// Track changes
function onCommentsChange() {
  hasChanges.value = true;
}

async function saveNotes() {
  if (!props.response) return;

  isSaving.value = true;

  try {
    const data: UpdateNotesRequest = {
      comments: comments.value,
    };

    emit("save", data);
    hasChanges.value = false;
  } catch (error) {
    console.error("Failed to save notes:", error);
  } finally {
    isSaving.value = false;
  }
}

function cancelChanges() {
  if (props.response) {
    comments.value = props.response.comments || "";
  }
  hasChanges.value = false;
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold flex items-center gap-2">
          <StickyNote class="w-5 h-5" />
          Notes & Action Plan
        </h3>
        <p class="text-sm text-muted-foreground">
          Add comments and track action items for this requirement
        </p>
      </div>
    </div>

    <!-- Notes Form -->
    <Card>
      <CardHeader>
        <CardTitle class="text-base flex items-center gap-2">
          <FileText class="w-4 h-4" />
          Comments
        </CardTitle>
        <CardDescription>
          Add any notes, observations, or additional context for this assessment
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div class="space-y-2">
            <Label for="comments">Assessment Comments</Label>
            <Textarea
              id="comments"
              v-model="comments"
              placeholder="Enter your comments about this requirement..."
              :rows="4"
              :disabled="!response"
              @input="onCommentsChange"
            />
          </div>
        </div>
        <!-- Save Actions -->
        <div
          v-if="response"
          class="flex items-center justify-end gap-4 pt-4 border-t"
        >
          <Button v-if="hasChanges" variant="outline" @click="cancelChanges">
            Cancel
          </Button>
          <Button
            variant="default"
            :disabled="!hasChanges || isSaving"
            @click="saveNotes"
          >
            <Save class="w-4 h-4 mr-2" />
            <span v-if="isSaving">Saving...</span>
            <span v-else>Save Notes</span>
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <div
      v-if="!response"
      class="text-center py-12 border-2 border-dashed rounded-lg"
    >
      <StickyNote class="w-12 h-12 mx-auto text-muted-foreground mb-4" />
      <h3 class="text-lg font-medium mb-2">No Requirement Selected</h3>
      <p class="text-sm text-muted-foreground">
        Select a requirement from the tree to view and edit notes
      </p>
    </div>
  </div>
</template>
