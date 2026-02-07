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
        <h1 class="text-3xl font-bold">Organization Detail</h1>
        <p class="text-gray-600 mt-2">View organization information</p>
      </div>
    </div>

    <div v-if="isLoading" class="bg-white p-8 rounded-lg shadow text-center">
      <p class="text-gray-500">Loading organization details...</p>
    </div>

    <div v-else-if="error" class="bg-white p-8 rounded-lg shadow text-center">
      <p class="text-red-600">Error loading organization details.</p>
    </div>

    <div v-else-if="organization" class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">Organization Information</h2>

      <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <dt class="text-sm font-medium text-gray-500">Name</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ organization.name }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Status</dt>
          <dd class="mt-1">
            <span
              :class="[
                'px-2 py-1 rounded text-xs',
                organization.isActive
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-800',
              ]"
            >
              {{ organization.isActive ? 'Active' : 'Inactive' }}
            </span>
          </dd>
        </div>

        <div class="md:col-span-2">
          <dt class="text-sm font-medium text-gray-500">Description</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ organization.description || '-' }}
          </dd>
        </div>

        <div v-if="organization.userCount !== undefined">
          <dt class="text-sm font-medium text-gray-500">Total Users</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ organization.userCount }}</dd>
        </div>

        <div v-if="organization.activeUserCount !== undefined">
          <dt class="text-sm font-medium text-gray-500">Active Users</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ organization.activeUserCount }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Created At</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(organization.createdAt).toLocaleString() }}
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Updated At</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(organization.updatedAt).toLocaleString() }}
          </dd>
        </div>
      </dl>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ArrowLeft as ArrowLeftIcon } from 'lucide-vue-next'
import { useOrganization } from '../composables/useOrganizations'

const route = useRoute()
const organizationId = computed(() => route.params.id as string)

const { data: organization, isLoading, error } = useOrganization(organizationId)
</script>
