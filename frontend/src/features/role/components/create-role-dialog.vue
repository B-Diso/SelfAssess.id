<script setup lang="ts">
import { watch } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Button } from '@/components/ui/button'
import { roleSchema } from '@/lib/validation/schemas/role'
import type { RoleForm } from '@/lib/validation/schemas/role'
import type { CreateRoleRequest } from '../types/role.types'

const props = defineProps<{
  open: boolean
  isSubmitting: boolean
  availablePermissions: string[]
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'submit', payload: CreateRoleRequest): void
}>()

const { handleSubmit, errors, isSubmitting: formIsSubmitting, defineField, setFieldValue, resetForm } = useForm<RoleForm>({
  validationSchema: toTypedSchema(roleSchema),
  initialValues: {
    name: '',
    permissions: [],
  },
})

const [name, nameAttrs] = defineField('name')
const [permissions] = defineField('permissions')

function isPermissionSelected(permission: string): boolean {
  return permissions.value?.includes(permission) ?? false
}

function setPermission(permission: string, checked: boolean) {
  const currentPermissions = permissions.value || []
  if (checked) {
    setFieldValue('permissions', [...currentPermissions, permission])
  } else {
    setFieldValue('permissions', currentPermissions.filter((p) => p !== permission))
  }
}

// Format permission label for display (e.g., "create-user" -> "Create User")
function formatPermissionLabel(permission: string): string {
  // Handle hyphen-separated format like "create-user", "view-organizations"
  const parts = permission.split('-')
  if (parts.length < 2) return permission
  
  const actionMap: Record<string, string> = {
    create: 'Create',
    view: 'View',
    update: 'Update',
    delete: 'Delete',
    manage: 'Manage',
    transfer: 'Transfer',
    assign: 'Assign',
    review: 'Review',
  }
  
  const action = parts[0]!
  const resource = parts.slice(1).join(' ')
  
  const actionText = actionMap[action] || action.charAt(0).toUpperCase() + action.slice(1)
  const resourceText = resource.charAt(0).toUpperCase() + resource.slice(1)
  
  return `${actionText} ${resourceText}`
}

function closeDialog() {
  emit('update:open', false)
}

const onSubmit = handleSubmit((values) => {
  emit('submit', {
    name: values.name.trim(),
    permissions: values.permissions,
  })
})

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
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Create New Role</DialogTitle>
        <DialogDescription>
          Create a new custom role with specific permissions.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="create-role-name">Role Name</Label>
          <Input
            id="create-role-name"
            v-model="name"
            v-bind="nameAttrs"
            placeholder="Enter role name"
            :class="{ 'border-destructive': errors.name }"
          />
          <p v-if="errors.name" class="text-sm text-destructive">
            {{ errors.name }}
          </p>
        </div>
        <div class="space-y-2">
          <Label>Permissions</Label>
          <div class="grid max-h-60 grid-cols-2 gap-2 overflow-y-auto rounded border p-2">
            <div
              v-for="permission in availablePermissions"
              :key="permission"
              class="flex items-center space-x-2"
            >
              <Checkbox
                :id="`create-role-${permission}`"
                :model-value="isPermissionSelected(permission)"
                @update:model-value="(value: boolean | 'indeterminate') => { if (typeof value === 'boolean') setPermission(permission, value) }"
              />
              <Label :for="`create-role-${permission}`" class="cursor-pointer text-sm font-normal">
                {{ formatPermissionLabel(permission) }}
              </Label>
            </div>
          </div>
          <p v-if="errors.permissions" class="text-sm text-destructive">
            {{ errors.permissions }}
          </p>
        </div>
      </form>
      <DialogFooter>
        <Button variant="outline" @click="closeDialog" :disabled="isSubmitting">Cancel</Button>
        <Button @click="onSubmit" :disabled="isSubmitting || formIsSubmitting">
          {{ isSubmitting ? 'Creating...' : 'Create Role' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
