<script setup lang="ts">
import { watch, computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import type { Assessment } from '../../types/assessment.types'

const props = defineProps<{
  open: boolean
  assessment: Assessment | null
  isSubmitting: boolean
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'submit', payload: {
    name: string
    periodValue?: string
    startDate?: string
    endDate?: string
  }): void
}>()

// Computed to determine if form should be disabled
// Draft and Active assessments can be edited
// Once submitted for review (pending_review+), becomes read-only
const isFormDisabled = computed(() => {
  if (!props.assessment) return false
  return !['draft', 'active'].includes(props.assessment.status)
})

// Status label for read-only badge
const statusLabel = computed(() => {
  if (!props.assessment) return ''
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    active: 'Active',
    pending_review: 'Pending Review',
    reviewed: 'Reviewed',
    pending_finish: 'Pending Finish',
    finished: 'Finished',
    rejected: 'Rejected',
    cancelled: 'Cancelled',
  }
  return statusMap[props.assessment.status] || props.assessment.status
})

// Form validation schema
const editSchema = toTypedSchema(
  z.object({
    name: z.string().min(1, 'Name is required').max(255),
    periodValue: z.string().max(255, 'Period value must be less than 255 characters').optional(),
    startDate: z.string().optional(),
    endDate: z.string().optional(),
  })
)

const { handleSubmit, errors, defineField, resetForm } = useForm({
  validationSchema: editSchema,
  initialValues: {
    name: '',
    periodValue: '',
    startDate: '',
    endDate: '',
  },
})

const [name, nameAttrs] = defineField('name')
const [periodValue, periodValueAttrs] = defineField('periodValue')
const [startDate, startDateAttrs] = defineField('startDate')
const [endDate, endDateAttrs] = defineField('endDate')

// Watch for assessment changes
watch(
  () => props.assessment,
  (assessment) => {
    if (assessment) {
      resetForm({
        values: {
          name: assessment.name,
          periodValue: assessment.periodValue || '',
          startDate: assessment.startDate || '',
          endDate: assessment.endDate || '',
        },
      })
    }
  },
  { immediate: true }
)

const onSubmit = handleSubmit((values: any) => {
  emit('submit', {
    name: values.name.trim(),
    periodValue: values.periodValue || undefined,
    startDate: values.startDate || undefined,
    endDate: values.endDate || undefined,
  })
})

function closeDialog() {
  emit('update:open', false)
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <div class="flex items-center gap-2">
          <DialogTitle>Edit Assessment</DialogTitle>
          <Badge v-if="isFormDisabled" variant="secondary" class="text-xs">
            Read Only
          </Badge>
        </div>
        <DialogDescription>
          <span v-if="isFormDisabled">
            This assessment is in <span class="font-semibold">{{ statusLabel }}</span> status and cannot be edited.
          </span>
          <span v-else>
            Update assessment details for {{ assessment?.name }}.
          </span>
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="onSubmit" class="space-y-4 py-4">
        <!-- Name -->
        <div class="space-y-2">
          <Label for="edit-name">Assessment Name</Label>
          <Input
            id="edit-name"
            v-model="name"
            v-bind="nameAttrs"
            placeholder="Enter assessment name"
            :disabled="isFormDisabled"
            :class="{
              'border-destructive': errors.name,
              'opacity-50 cursor-not-allowed': isFormDisabled
            }"
          />
          <p v-if="errors.name" class="text-sm text-destructive">
            {{ errors.name }}
          </p>
        </div>

        <!-- Period Value -->
        <div class="space-y-2">
          <Label for="edit-period-value">Period Value</Label>
          <Input
            id="edit-period-value"
            v-model="periodValue"
            v-bind="periodValueAttrs"
            placeholder="e.g., April 2025, Q1 2025, Semester 1 2025"
            :disabled="isFormDisabled"
            :class="{
              'border-destructive': errors.periodValue,
              'opacity-50 cursor-not-allowed': isFormDisabled
            }"
          />
          <p v-if="errors.periodValue" class="text-sm text-destructive">
            {{ errors.periodValue }}
          </p>
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="edit-start-date">Start Date</Label>
            <Input
              id="edit-start-date"
              v-model="startDate"
              v-bind="startDateAttrs"
              type="date"
              :disabled="isFormDisabled"
              :class="{ 'opacity-50 cursor-not-allowed': isFormDisabled }"
            />
          </div>
          <div class="space-y-2">
            <Label for="edit-end-date">End Date</Label>
            <Input
              id="edit-end-date"
              v-model="endDate"
              v-bind="endDateAttrs"
              type="date"
              :disabled="isFormDisabled"
              :class="{ 'opacity-50 cursor-not-allowed': isFormDisabled }"
            />
          </div>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          @click="closeDialog"
          :disabled="isSubmitting"
        >
          {{ isFormDisabled ? 'Close' : 'Cancel' }}
        </Button>
        <template v-if="!isFormDisabled">
          <Button @click="onSubmit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
          </Button>
        </template>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
