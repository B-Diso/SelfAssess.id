<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { organizationSchema } from '@/lib/validation/schemas/organization'
import type { Organization, UpdateOrganizationRequest } from '../types/organization.types'

const props = defineProps<{
  open: boolean
  isSubmitting: boolean
  organization: Organization | null
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'submit', payload: UpdateOrganizationRequest): void
}>()

const { handleSubmit, errors, defineField, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(organizationSchema),
  initialValues: {
    name: '',
    description: '',
    isActive: true,
  },
})

const [name, nameAttrs] = defineField('name')
const [description, descriptionAttrs] = defineField('description')
const [isActive] = defineField('isActive')

const statusValue = computed({
  get: () => (isActive.value ? 'true' : 'false'),
  set: (value) => {
    isActive.value = value === 'true'
  },
})

function syncForm(organization: Organization | null) {
  if (!organization) {
    resetForm()
    return
  }

  setValues({
    name: organization.name,
    description: organization.description ?? '',
    isActive: organization.isActive,
  })
}

const onSubmit = handleSubmit((values) => {
  if (!props.organization) {
    return
  }

  emit('submit', {
    name: values.name.trim(),
    description: values.description?.trim() ?? '',
    isActive: values.isActive,
  })
})

function closeDialog() {
  emit('update:open', false)
}

watch(
  () => props.organization,
  (organization) => {
    if (props.open) {
      syncForm(organization)
    }
  },
  { immediate: true },
)

watch(
  () => props.open,
  (open) => {
    if (open) {
      syncForm(props.organization)
    }
  },
)
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Edit Organization</DialogTitle>
        <DialogDescription>
          Update organization information for {{ organization?.name }}.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="edit-org-name">Name</Label>
          <Input
            id="edit-org-name"
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
          <Label for="edit-org-description">Description</Label>
          <Input
            id="edit-org-description"
            v-model="description"
            v-bind="descriptionAttrs"
            placeholder="Enter description (optional)"
            :class="{ 'border-destructive': errors.description }"
          />
          <p v-if="errors.description" class="text-sm text-destructive">
            {{ errors.description }}
          </p>
        </div>
        <div class="space-y-2">
          <Label>Status</Label>
          <Select v-model="statusValue">
            <SelectTrigger>
              <SelectValue :placeholder="isActive ? 'Active' : 'Inactive'" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="true">Active</SelectItem>
              <SelectItem value="false">Inactive</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </form>
      <DialogFooter>
        <Button variant="outline" @click="closeDialog">Cancel</Button>
        <Button @click="onSubmit" :disabled="isSubmitting || !organization">
          {{ isSubmitting ? 'Updating...' : 'Update Organization' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
