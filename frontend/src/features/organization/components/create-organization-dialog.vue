<script setup lang="ts">
import { watch } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { organizationSchema } from '@/lib/validation/schemas/organization'
import type { CreateOrganizationRequest } from '../types/organization.types'

const props = defineProps<{
  open: boolean
  isSubmitting: boolean
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'submit', payload: CreateOrganizationRequest): void
}>()

const { handleSubmit, errors, defineField, resetForm } = useForm({
  validationSchema: toTypedSchema(organizationSchema),
  initialValues: {
    name: '',
    description: '',
    isActive: true,
  },
})

const [name, nameAttrs] = defineField('name')
const [description, descriptionAttrs] = defineField('description')

const onSubmit = handleSubmit((values) => {
  const payload: CreateOrganizationRequest = {
    name: values.name.trim(),
  }

  const desc = values.description?.trim()
  if (desc) {
    payload.description = desc
  }

  emit('submit', payload)
})

function closeDialog() {
  emit('update:open', false)
}

watch(
  () => props.open,
  (open) => {
    if (!open) {
      resetForm()
    }
  },
)
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Create New Organization</DialogTitle>
        <DialogDescription>
          Add a new organization to the system. Fill in the required information below.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="create-org-name">Name</Label>
          <Input
            id="create-org-name"
            v-model="name"
            v-bind="nameAttrs"
            placeholder="Enter organization name"
            :class="{ 'border-destructive': errors.name }"
          />
          <p v-if="errors.name" class="text-sm text-destructive">
            {{ errors.name }}
          </p>
        </div>
        <div class="space-y-2">
          <Label for="create-org-description">Description</Label>
          <Input
            id="create-org-description"
            v-model="description"
            v-bind="descriptionAttrs"
            placeholder="Enter description (optional)"
            :class="{ 'border-destructive': errors.description }"
          />
          <p v-if="errors.description" class="text-sm text-destructive">
            {{ errors.description }}
          </p>
        </div>
      </form>
      <DialogFooter>
        <Button variant="outline" @click="closeDialog">Cancel</Button>
        <Button @click="onSubmit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Creating...' : 'Create Organization' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
