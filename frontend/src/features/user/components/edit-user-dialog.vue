<script setup lang="ts">
import { computed, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import type { UpdateUserRequest, User } from '../types/user.types'
import { useForm, toTypedSchema } from '@/lib/validation'
import { updateUserSchema, type UpdateUserForm } from '@/lib/validation/schemas/user'

const props = defineProps<{
  open: boolean
  isSubmitting: boolean
  user: User | null
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'submit', payload: UpdateUserRequest): void
}>()

const { handleSubmit, values, errors, resetForm, setFieldValue, defineField } = useForm<UpdateUserForm>({
  validationSchema: toTypedSchema(updateUserSchema),
  initialValues: {
    name: '',
    email: '',
    password: '',
  },
})

const onSubmit = handleSubmit((formValues) => {
  if (!props.user) return

  const payload: UpdateUserRequest = {}

  if (formValues.name !== undefined && formValues.name !== props.user.name) {
    payload.name = formValues.name
  }

  if (formValues.email !== undefined && formValues.email !== props.user.email) {
    payload.email = formValues.email
  }

  if (formValues.password !== undefined && formValues.password.trim().length > 0) {
    payload.password = formValues.password.trim()
  }

  // Only emit if there are actual changes
  if (Object.keys(payload).length > 0) {
    emit('submit', payload)
  }
})

const hasChanges = computed(() => {
  if (!props.user) return false

  const nameChanged = values.name !== props.user.name
  const emailChanged = values.email !== props.user.email
  const passwordChanged = values.password && values.password.trim().length > 0

  return nameChanged || emailChanged || passwordChanged
})

const dialogDescription = computed(() =>
  props.user ? `Update user information for ${props.user.name}.` : 'Select a user to edit.',
)

// Define fields dengan defineField
const [name, nameAttrs] = defineField('name')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

function closeDialog() {
  emit('update:open', false)
}

function handleClose() {
  closeDialog()
}

function syncForm(user: User | null) {
  if (!user) {
    resetForm()
    return
  }

  setFieldValue('name', user.name)
  setFieldValue('email', user.email)
  setFieldValue('password', '')
}

watch(
  () => props.user,
  user => {
    if (props.open) {
      syncForm(user)
    }
  },
  { immediate: true },
)

watch(
  () => props.open,
  open => {
    if (open) {
      syncForm(props.user)
    } else {
      setFieldValue('password', '')
    }
  },
)
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Edit User</DialogTitle>
        <DialogDescription>{{ dialogDescription }}</DialogDescription>
      </DialogHeader>
      <form @submit="onSubmit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="edit-user-name">Name</Label>
          <Input 
            id="edit-user-name" 
            v-model="name" 
            v-bind="nameAttrs"
            placeholder="Enter user name"
            :class="{ 'border-destructive': errors.name }"
          />
          <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
        </div>
        <div class="space-y-2">
          <Label for="edit-user-email">Email</Label>
          <Input
            id="edit-user-email"
            v-model="email"
            v-bind="emailAttrs"
            type="email"
            placeholder="Enter email address"
            :class="{ 'border-destructive': errors.email }"
          />
          <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
        </div>
        <div class="space-y-2">
          <Label for="edit-user-password">New Password (Optional)</Label>
          <Input
            id="edit-user-password"
            v-model="password"
            v-bind="passwordAttrs"
            type="password"
            placeholder="Leave blank to keep current password"
            :class="{ 'border-destructive': errors.password }"
          />
          <p v-if="errors.password" class="text-sm text-destructive">{{ errors.password }}</p>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
          <Button type="submit" :disabled="isSubmitting || !hasChanges">
            {{ isSubmitting ? 'Updating...' : 'Update User' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
