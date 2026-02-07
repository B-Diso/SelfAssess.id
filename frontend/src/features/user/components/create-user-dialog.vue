<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { ChevronsUpDownIcon, CheckIcon } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from '@/components/ui/command'
import { cn } from '@/lib/utils'
import { organizationApi } from '@/features/organization/api/organizationApi'
import type { Organization } from '@/features/organization/types/organization.types'
import type { CreateUserRequest } from '../types/user.types'
import { useForm, toTypedSchema } from '@/lib/validation'
import { createUserSchema, type CreateUserForm } from '@/lib/validation/schemas/user'

type RoleOption = {
  label: string
  value: string
}

const props = defineProps<{
  open: boolean
  isSubmitting: boolean
  roleOptions: RoleOption[]
  isSuperAdmin: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'submit', payload: CreateUserRequest & { organizationId?: string }): void
}>()

const organizationPopoverOpen = ref(false)

const organizationsQueryEnabled = computed(() => props.isSuperAdmin && props.open)

const {
  data: organizationsResponse,
  isLoading: organizationsLoading,
} = useQuery({
  queryKey: ['organizations', 'create-user-dialog'],
  queryFn: () => organizationApi.getOrganizations({ perPage: 100, sortBy: 'name', sortOrder: 'asc' }),
  enabled: organizationsQueryEnabled,
})

const organizations = computed<Organization[]>(() => organizationsResponse.value?.data ?? [])

const { handleSubmit, values, errors, resetForm, setFieldValue, meta, defineField } = useForm<CreateUserForm>({
  validationSchema: toTypedSchema(createUserSchema),
  initialValues: {
    name: '',
    email: '',
    password: '',
    organizationId: '',
    roles: [] as string[],
  },
})

const selectedOrganization = computed(() =>
  organizations.value.find(organization => organization.id === values.organizationId) ?? null,
)

// Define fields dengan defineField
const [name, nameAttrs] = defineField('name')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

const dialogDescription = computed(() =>
  props.isSuperAdmin
    ? 'Select the target organization and role for the new user.'
    : 'Add a new user to your organization. The organization is assigned automatically.',
)

const onSubmit = handleSubmit((formValues) => {
  const payload: CreateUserRequest & { organizationId?: string } = {
    name: formValues.name,
    email: formValues.email,
    password: formValues.password,
    role: formValues.roles[0]!,
  }

  // For Super Admin, include organizationId in payload
  if (props.isSuperAdmin && formValues.organizationId) {
    payload.organizationId = formValues.organizationId
  }

  emit('submit', payload)
})

function closeDialog() {
  emit('update:open', false)
}

function handleClose() {
  closeDialog()
}

watch(
  () => props.roleOptions,
  options => {
    if (!options.length) {
      setFieldValue('roles', [])
      return
    }

    if (!values.roles.some((role: string) => options.some(option => option.value === role))) {
      const [firstOption] = options
      if (firstOption) {
        setFieldValue('roles', [firstOption.value])
      }
    }
  },
  { immediate: true },
)

watch(
  () => props.open,
  open => {
    if (!open) {
      resetForm()
      organizationPopoverOpen.value = false
    }
  },
)

onMounted(() => {
  resetForm()
  organizationPopoverOpen.value = false
})
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Create New User</DialogTitle>
        <DialogDescription>
          {{ dialogDescription }}
        </DialogDescription>
      </DialogHeader>

      <form @submit="onSubmit" class="space-y-4 py-4">
        <div v-if="isSuperAdmin" class="space-y-2">
          <Label for="organization">Organization *</Label>
          <Popover v-model:open="organizationPopoverOpen">
            <PopoverTrigger as-child>
              <Button
                variant="outline"
                role="combobox"
                :aria-expanded="organizationPopoverOpen"
                class="w-full justify-between"
                :class="{ 'border-red-500': meta.touched && !values.organizationId }"
              >
                {{ selectedOrganization?.name || 'Select organization...' }}
                <ChevronsUpDownIcon class="ml-2 h-4 w-4 shrink-0 opacity-50" />
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-full p-0">
              <Command>
                <CommandInput placeholder="Search organizations..." />
                <CommandEmpty v-if="!organizationsLoading">No organization found.</CommandEmpty>
                <CommandGroup v-if="!organizationsLoading">
                  <CommandItem
                    v-for="org in organizations"
                    :key="org.id"
                    :value="org.id"
                    @select="() => {
                      setFieldValue('organizationId', org.id)
                      organizationPopoverOpen = false
                    }"
                  >
                    <CheckIcon :class="cn('mr-2 h-4 w-4', values.organizationId === org.id ? 'opacity-100' : 'opacity-0')" />
                    {{ org.name }}
                  </CommandItem>
                </CommandGroup>
              </Command>
            </PopoverContent>
          </Popover>
        </div>

        <div class="space-y-2">
          <Label for="create-name">Name</Label>
          <Input 
            id="create-name" 
            v-model="name" 
            v-bind="nameAttrs"
            placeholder="Enter user name"
            :class="{ 'border-destructive': errors.name }"
          />
          <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
        </div>
        <div class="space-y-2">
          <Label for="create-email">Email</Label>
          <Input 
            id="create-email" 
            v-model="email" 
            v-bind="emailAttrs"
            type="email" 
            placeholder="Enter email address"
            :class="{ 'border-destructive': errors.email }"
          />
          <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
        </div>
        <div class="space-y-2">
          <Label for="create-password">Password</Label>
          <Input
            id="create-password"
            v-model="password"
            v-bind="passwordAttrs"
            type="password"
            placeholder="Enter password"
            :class="{ 'border-destructive': errors.password }"
          />
          <p v-if="errors.password" class="text-sm text-destructive">{{ errors.password }}</p>
        </div>
        <div v-if="roleOptions.length" class="space-y-2">
          <Label for="create-role">Role</Label>
          <Popover>
            <PopoverTrigger as-child>
              <Button
                variant="outline"
                role="combobox"
                class="w-full justify-between"
              >
                {{ values.roles.length > 0 
                  ? roleOptions.filter(opt => values.roles.includes(opt.value)).map(opt => opt.label).join(', ')
                  : 'Select roles...' }}
                <ChevronsUpDownIcon class="ml-2 h-4 w-4 shrink-0 opacity-50" />
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-full p-0">
              <Command>
                <CommandInput placeholder="Search roles..." />
                <CommandEmpty>No role found.</CommandEmpty>
                <CommandGroup>
                  <CommandItem
                    v-for="option in roleOptions"
                    :key="option.value"
                    :value="option.value"
                    @select="() => {
                      const currentRoles = values.roles
                      const index = currentRoles.indexOf(option.value)
                      if (index === -1) {
                        setFieldValue('roles', [...currentRoles, option.value])
                      } else {
                        setFieldValue('roles', currentRoles.filter((r: string) => r !== option.value))
                      }
                    }"
                  >
                    <CheckIcon :class="cn('mr-2 h-4 w-4', values.roles.includes(option.value) ? 'opacity-100' : 'opacity-0')" />
                    {{ option.label }}
                  </CommandItem>
                </CommandGroup>
              </Command>
            </PopoverContent>
          </Popover>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
          <Button type="submit" :disabled="isSubmitting || !meta.valid">
            {{ isSubmitting ? 'Creating...' : 'Create User' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
