<script setup lang="ts">
import { ref, watch } from 'vue'
import { toast } from 'vue-sonner'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Skeleton } from '@/components/ui/skeleton'
import {
  User,
  Mail,
  Building2,
  Shield,
  Clock,
  Key,
  Loader2,
  Pencil,
  X,
  Check,
  Lock,
} from 'lucide-vue-next'
import { useUserStore } from '../stores/userStore'
import { authApi } from '../api/authApi'
import { storeToRefs } from 'pinia'
import { profileSchema, passwordSchema } from '@/lib/validation/schemas/auth'

// User Store
const userStore = useUserStore()
const { user } = storeToRefs(userStore)

// Edit Mode States
const isEditingProfile = ref(false)
const isEditingPassword = ref(false)

// Profile Form with vee-validate + zod
const {
  handleSubmit: handleProfileSubmit,
  errors: profileErrors,
  isSubmitting: isSavingProfile,
  defineField: defineProfileField,
  resetForm: resetProfileForm,
  setValues: setProfileValues,
} = useForm({
  validationSchema: toTypedSchema(profileSchema),
  initialValues: {
    name: '',
    email: '',
  },
})

const [profileName, profileNameAttrs] = defineProfileField('name')
const [profileEmail, profileEmailAttrs] = defineProfileField('email')

// Password Form with vee-validate + zod
const {
  handleSubmit: handlePasswordSubmit,
  errors: passwordErrors,
  isSubmitting: isSavingPassword,
  defineField: definePasswordField,
  resetForm: resetPasswordForm,
} = useForm({
  validationSchema: toTypedSchema(passwordSchema),
  initialValues: {
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
  },
})

const [currentPassword, currentPasswordAttrs] = definePasswordField('currentPassword')
const [newPassword, newPasswordAttrs] = definePasswordField('newPassword')
const [confirmPassword, confirmPasswordAttrs] = definePasswordField('confirmPassword')

// Watch for user changes to update form initial values
watch(
  () => user.value,
  (newUser) => {
    if (newUser) {
      setProfileValues({
        name: newUser.name || '',
        email: newUser.email || '',
      })
    }
  },
  { immediate: true }
)

// Format date helper
const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Never'
  return new Date(dateString).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

// Toggle edit modes
const toggleEditProfile = () => {
  if (isEditingProfile.value) {
    // Cancel edit - reset form
    resetProfileForm({
      values: {
        name: user.value?.name || '',
        email: user.value?.email || '',
      },
    })
  } else {
    // Start edit - initialize form with current values
    setProfileValues({
      name: user.value?.name || '',
      email: user.value?.email || '',
    })
  }
  isEditingProfile.value = !isEditingProfile.value
  isEditingPassword.value = false
}

const toggleEditPassword = () => {
  if (isEditingPassword.value) {
    // Cancel edit - reset form
    resetPasswordForm()
  }
  isEditingPassword.value = !isEditingPassword.value
  isEditingProfile.value = false
}

// Submit handlers
const onSubmitProfile = handleProfileSubmit(async (values) => {
  if (!user.value) return

  try {
    const response = await authApi.updateProfile({
      name: values.name,
      email: values.email,
    })

    // Update local user store
    userStore.updateUser(response.data)

    toast.success('Profile updated successfully')
    isEditingProfile.value = false
  } catch (error: any) {
    const message = error.response?.data?.error?.message || 'Failed to update profile'
    toast.error(message)
  }
})

const onSubmitPassword = handlePasswordSubmit(async (values) => {
  try {
    await authApi.updatePassword({
      currentPassword: values.currentPassword,
      password: values.newPassword,
      passwordConfirmation: values.confirmPassword,
    })

    toast.success('Password updated successfully')
    // Reset form
    resetPasswordForm()
    isEditingPassword.value = false
  } catch (error: any) {
    const message = error.response?.data?.error?.message || 'Failed to update password'
    toast.error(message)
  }
})
</script>

<template>
  <div class="space-y-6 p-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Profile</h1>
      <p class="text-muted-foreground mt-1">
        Manage your account settings and password
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="!user" class="space-y-4">
      <Card>
        <CardHeader>
          <Skeleton class="h-6 w-32" />
          <Skeleton class="h-4 w-48" />
        </CardHeader>
        <CardContent class="space-y-4">
          <Skeleton class="h-10 w-full" />
          <Skeleton class="h-10 w-full" />
        </CardContent>
      </Card>
    </div>

    <template v-else>
      <!-- Profile Information Card -->
      <Card>
        <CardHeader class="flex flex-row items-start justify-between space-y-0">
          <div>
            <CardTitle class="flex items-center gap-2">
              <User class="h-5 w-5" />
              Profile Information
            </CardTitle>
            <CardDescription>
              Update your personal information
            </CardDescription>
          </div>
          <Button
            v-if="!isEditingProfile"
            variant="outline"
            size="sm"
            @click="toggleEditProfile"
          >
            <Pencil class="h-4 w-4 mr-2" />
            Edit
          </Button>
        </CardHeader>

        <CardContent>
          <!-- View Mode -->
          <div v-if="!isEditingProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label class="text-muted-foreground">Full Name</Label>
                <div class="flex items-center gap-2">
                  <User class="h-4 w-4 text-muted-foreground" />
                  <span class="font-medium">{{ user.name }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-muted-foreground">Email Address</Label>
                <div class="flex items-center gap-2">
                  <Mail class="h-4 w-4 text-muted-foreground" />
                  <span class="font-medium">{{ user.email }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-muted-foreground">Organization</Label>
                <div class="flex items-center gap-2">
                  <Building2 class="h-4 w-4 text-muted-foreground" />
                  <span class="font-medium">{{ user.organizationName }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-muted-foreground">Last Login</Label>
                <div class="flex items-center gap-2">
                  <Clock class="h-4 w-4 text-muted-foreground" />
                  <span class="font-medium">{{ formatDate(user.lastLoginAt) }}</span>
                </div>
              </div>
            </div>

            <Separator class="my-4" />

            <div class="space-y-2">
              <Label class="text-muted-foreground">Roles</Label>
              <div class="flex items-center gap-2 flex-wrap">
                <Shield class="h-4 w-4 text-muted-foreground" />
                <span
                  v-for="role in user.roles"
                  :key="role"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary"
                >
                  {{ role.replace('_', ' ') }}
                </span>
              </div>
            </div>
          </div>

          <!-- Edit Mode -->
          <form v-else @submit.prevent="onSubmitProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="name">Full Name</Label>
                <Input
                  id="name"
                  v-model="profileName"
                  v-bind="profileNameAttrs"
                  placeholder="Enter your name"
                  :disabled="isSavingProfile"
                  :class="{ 'border-destructive': profileErrors.name }"
                />
                <p v-if="profileErrors.name" class="text-sm text-destructive">
                  {{ profileErrors.name }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="email">Email Address</Label>
                <Input
                  id="email"
                  v-model="profileEmail"
                  v-bind="profileEmailAttrs"
                  type="email"
                  placeholder="Enter your email"
                  :disabled="isSavingProfile"
                  :class="{ 'border-destructive': profileErrors.email }"
                />
                <p v-if="profileErrors.email" class="text-sm text-destructive">
                  {{ profileErrors.email }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
              <Button
                type="submit"
                :disabled="isSavingProfile"
              >
                <Loader2 v-if="isSavingProfile" class="mr-2 h-4 w-4 animate-spin" />
                <Check v-else class="mr-2 h-4 w-4" />
                Save Changes
              </Button>
              <Button
                type="button"
                variant="outline"
                @click="toggleEditProfile"
                :disabled="isSavingProfile"
              >
                <X class="mr-2 h-4 w-4" />
                Cancel
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Password Change Card -->
      <Card>
        <CardHeader class="flex flex-row items-start justify-between space-y-0">
          <div>
            <CardTitle class="flex items-center gap-2">
              <Lock class="h-5 w-5" />
              Change Password
            </CardTitle>
            <CardDescription>
              Update your password to keep your account secure
            </CardDescription>
          </div>
          <Button
            v-if="!isEditingPassword"
            variant="outline"
            size="sm"
            @click="toggleEditPassword"
          >
            <Key class="h-4 w-4 mr-2" />
            Change
          </Button>
        </CardHeader>

        <CardContent>
          <!-- View Mode -->
          <div v-if="!isEditingPassword" class="text-sm text-muted-foreground">
            <p>
              Your password was last updated on
              <span class="font-medium">{{ formatDate(user.updatedAt) }}</span>
            </p>
          </div>

          <!-- Edit Mode -->
          <form v-else @submit.prevent="onSubmitPassword" class="space-y-4">
            <div class="space-y-2">
              <Label for="currentPassword">Current Password</Label>
              <Input
                id="currentPassword"
                v-model="currentPassword"
                v-bind="currentPasswordAttrs"
                type="password"
                placeholder="Enter current password"
                :disabled="isSavingPassword"
                :class="{ 'border-destructive': passwordErrors.currentPassword }"
              />
              <p v-if="passwordErrors.currentPassword" class="text-sm text-destructive">
                {{ passwordErrors.currentPassword }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="newPassword">New Password</Label>
              <Input
                id="newPassword"
                v-model="newPassword"
                v-bind="newPasswordAttrs"
                type="password"
                placeholder="Enter new password (min 8 characters)"
                :disabled="isSavingPassword"
                :class="{ 'border-destructive': passwordErrors.newPassword }"
              />
              <p v-if="passwordErrors.newPassword" class="text-sm text-destructive">
                {{ passwordErrors.newPassword }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="confirmPassword">Confirm New Password</Label>
              <Input
                id="confirmPassword"
                v-model="confirmPassword"
                v-bind="confirmPasswordAttrs"
                type="password"
                placeholder="Confirm new password"
                :disabled="isSavingPassword"
                :class="{ 'border-destructive': passwordErrors.confirmPassword }"
              />
              <p v-if="passwordErrors.confirmPassword" class="text-sm text-destructive">
                {{ passwordErrors.confirmPassword }}
              </p>
            </div>

            <div class="flex items-center gap-2 pt-2">
              <Button
                type="submit"
                :disabled="isSavingPassword"
              >
                <Loader2 v-if="isSavingPassword" class="mr-2 h-4 w-4 animate-spin" />
                <Check v-else class="mr-2 h-4 w-4" />
                Update Password
              </Button>
              <Button
                type="button"
                variant="outline"
                @click="toggleEditPassword"
                :disabled="isSavingPassword"
              >
                <X class="mr-2 h-4 w-4" />
                Cancel
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </template>
  </div>
</template>
