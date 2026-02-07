<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <button
        @click="$router.back()"
        class="p-2 hover:bg-gray-100 rounded-lg"
      >
        <ArrowLeftIcon class="h-5 w-5" />
      </button>
      <div>
        <h1 class="text-3xl font-bold">User Detail</h1>
        <p class="text-gray-600 mt-2">View user information</p>
      </div>
    </div>

    <div v-if="isLoading" class="bg-white p-8 rounded-lg shadow text-center">
      <p class="text-gray-500">Loading user details...</p>
    </div>

    <div v-else-if="error" class="bg-white p-8 rounded-lg shadow text-center">
      <p class="text-red-600">Error loading user details.</p>
    </div>

    <div v-else-if="user" class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">User Information</h2>

      <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <dt class="text-sm font-medium text-gray-500">Name</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ user.name }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Email</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ user.email }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Organization</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ user.organizationName }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Roles</dt>
          <dd class="mt-1 text-sm text-gray-900">
            <span
              v-for="role in user.roles"
              :key="role"
              class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-2"
            >
              {{ role }}
            </span>
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Status</dt>
          <dd class="mt-1">
            <span
              :class="[
                'px-2 py-1 rounded text-xs',
                user.emailVerifiedAt
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-800',
              ]"
            >
              {{ user.emailVerifiedAt ? 'Verified' : 'Unverified' }}
            </span>
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Last Login</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ user.lastLoginAt ? new Date(user.lastLoginAt).toLocaleString() : 'Never' }}
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Created At</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(user.createdAt).toLocaleString() }}
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Updated At</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(user.updatedAt).toLocaleString() }}
          </dd>
        </div>
      </dl>

      <div class="mt-6">
        <h3 class="text-lg font-semibold mb-2">Permissions</h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="permission in user.permissions"
            :key="permission"
            class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm"
          >
            {{ permission }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ArrowLeft as ArrowLeftIcon } from 'lucide-vue-next'
import { useUser } from '../composables/useUsers'

const route = useRoute()
const userId = computed(() => route.params.id as string)

const { data: user, isLoading, error } = useUser(userId)
</script>
