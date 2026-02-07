<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Loader2, AlertTriangle } from 'lucide-vue-next';
import { useTransitionAssessment } from '../composables/useAssessments';
import type { Assessment } from '../types/assessment.types';
import { toast } from 'vue-sonner';

// Props
const props = defineProps<{
  open: boolean;
  assessment: Assessment | null;
}>();

// Emits
const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'success'): void;
}>();

// Validation schema
const cancelSchema = z.object({
  reason: z
    .string()
    .min(1, 'Reason is required')
    .min(10, 'Reason must be at least 10 characters'),
});

const validationSchema = toTypedSchema(cancelSchema);

// Form setup
const { handleSubmit, resetForm, errors, defineField, meta } = useForm({
  validationSchema,
  initialValues: {
    reason: '',
  },
});

const [reason, reasonAttrs] = defineField('reason');

// Transition mutation
const transitionMutation = useTransitionAssessment();

const isSubmitting = computed(() => transitionMutation.isPending.value);
const isValid = computed(() => meta.value.valid);

// Form submission handler
const onSubmit = handleSubmit(async (values) => {
  if (!props.assessment) {
    return;
  }

  try {
    await transitionMutation.mutateAsync({
      id: props.assessment.id,
      payload: {
        status: 'cancelled',
        note: values.reason.trim(),
      },
    });

    toast.success('Assessment cancelled successfully');
    emit('success');
    closeDialog();
  } catch (error: any) {
    const errorMessage =
      error?.response?.data?.message || 'Failed to cancel assessment';
    toast.error(errorMessage);
  }
});

// Close dialog handler
function closeDialog() {
  emit('update:open', false);
}

// Reset form when dialog closes
watch(
  () => props.open,
  (newValue) => {
    if (!newValue) {
      resetForm();
    }
  }
);

// Assessment name display
const assessmentName = computed(() => {
  return props.assessment?.name ?? 'this assessment';
});
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2 text-destructive">
          <AlertTriangle class="h-5 w-5" />
          Cancel Assessment
        </DialogTitle>
        <DialogDescription>
          Are you sure you want to cancel <strong>{{ assessmentName }}</strong>?
          This action cannot be undone and the assessment will be permanently cancelled.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit" class="space-y-4 py-4">
        <!-- Reason Field -->
        <div class="space-y-2">
          <Label for="cancel-reason">
            Reason for Cancellation
            <span class="text-destructive">*</span>
          </Label>
          <Textarea
            id="cancel-reason"
            v-model="reason"
            v-bind="reasonAttrs"
            placeholder="Please provide a detailed reason for cancelling this assessment (minimum 10 characters)..."
            :disabled="isSubmitting"
            :class="{ 'border-destructive': errors.reason }"
            :rows="4"
          />
          <p v-if="errors.reason" class="text-xs text-destructive">
            {{ errors.reason }}
          </p>
          <p v-else class="text-xs text-muted-foreground">
            This reason will be recorded in the system for audit purposes.
          </p>
        </div>

        <DialogFooter class="gap-2 sm:gap-0">
          <Button
            type="button"
            variant="outline"
            @click="closeDialog"
            :disabled="isSubmitting"
          >
            Keep Assessment
          </Button>
          <Button
            type="submit"
            variant="destructive"
            :disabled="!isValid || isSubmitting"
          >
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            {{ isSubmitting ? 'Cancelling...' : 'Cancel Assessment' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
