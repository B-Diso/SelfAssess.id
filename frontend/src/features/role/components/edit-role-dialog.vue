<script setup lang="ts">
import { watch, computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Button } from '@/components/ui/button'
import { updateRoleSchema } from '@/lib/validation/schemas/role'
import type { UpdateRoleForm } from '@/lib/validation/schemas/role'
import type { Role, UpdateRoleRequest } from '../types/role.types'

const props = defineProps<{
  open: boolean
  isSubmitting: boolean
  role: Role | null
  availablePermissions: string[]
}>()

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
  (event: 'submit', payload: UpdateRoleRequest): void
}>()

// Get initial values from role
const getInitialValues = (): UpdateRoleForm => ({
  name: props.role?.name ?? '',
  permissions: props.role?.permissions ? [...props.role.permissions] : [],
})

const { handleSubmit, errors, isSubmitting: formIsSubmitting, defineField, setFieldValue, values, controlledValues } = useForm<UpdateRoleForm>({
  validationSchema: toTypedSchema(updateRoleSchema),
  initialValues: getInitialValues(),
})

const [name, nameAttrs] = defineField('name')

// Gunakan controlledValues untuk permissions agar lebih reliable
const currentPermissions = computed(() => {
  const perms = controlledValues.value?.permissions || values.permissions || []
  // Convert Proxy to plain array
  return Array.isArray(perms) ? [...perms] : []
})

function isPermissionSelected(permission: string): boolean {
  return currentPermissions.value.includes(permission)
}

function setPermission(permission: string, checked: boolean) {
  const perms = currentPermissions.value
  if (checked) {
    setFieldValue('permissions', [...perms, permission])
  } else {
    setFieldValue('permissions', perms.filter((p) => p !== permission))
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

const onSubmit = handleSubmit((formValues) => {
  if (!props.role) return

  emit('submit', {
    name: formValues.name?.trim(),
    permissions: formValues.permissions,
  })
})

// Watch untuk sync form ketika role berubah atau dialog dibuka
watch(
  () => ({ role: props.role, isOpen: props.open }),
  (newVal) => {
    const newRole = newVal.role
    const isOpen = newVal.isOpen
    
    if (isOpen && newRole) {
      // Convert permissions to plain array to avoid Proxy issues
      const permissions = newRole.permissions ? JSON.parse(JSON.stringify(newRole.permissions)) : []
      setFieldValue('name', newRole.name)
      setFieldValue('permissions', permissions)
    }
    // Reset ke default ketika dialog ditutup
    if (!isOpen) {
      setFieldValue('name', '')
      setFieldValue('permissions', [])
    }
  },
  { immediate: true, deep: true }
)
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Edit Role</DialogTitle>
        <DialogDescription>
          Update role information for {{ role?.name }}.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="edit-role-name">Role Name</Label>
          <Input
            id="edit-role-name"
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
                :id="`edit-role-${permission}`"
                :model-value="isPermissionSelected(permission)"
                @update:model-value="(value: boolean | 'indeterminate') => { if (typeof value === 'boolean') setPermission(permission, value) }"
              />
              <Label :for="`edit-role-${permission}`" class="cursor-pointer text-sm font-normal">
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
        <Button @click="onSubmit" :disabled="isSubmitting || formIsSubmitting || !role">
          {{ isSubmitting ? 'Updating...' : 'Update Role' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
