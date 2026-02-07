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
        <h1 class="text-3xl font-bold">Role Detail</h1>
        <p class="text-gray-600 mt-2">View role information and permissions</p>
      </div>
    </div>

    <div v-if="isLoading" class="bg-white p-8 rounded-lg shadow text-center">
      <p class="text-gray-500">Loading role details...</p>
    </div>

    <div v-else-if="error" class="bg-white p-8 rounded-lg shadow text-center">
      <p class="text-red-600">Error loading role details.</p>
    </div>

    <div v-else-if="role" class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">Role Information</h2>

      <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <dt class="text-sm font-medium text-gray-500">Role Name</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ formatRoleName(role.name) }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Type</dt>
          <dd class="mt-1">
            <span
              :class="[
                'px-2 py-1 rounded text-xs',
                isSystemRole(role.name)
                  ? 'bg-purple-100 text-purple-800'
                  : 'bg-blue-100 text-blue-800',
              ]"
            >
              {{ isSystemRole(role.name) ? 'System Role' : 'Custom Role' }}
            </span>
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Guard Name</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ role.guardName }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Total Permissions</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ role.permissions.length }}</dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Created At</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(role.createdAt).toLocaleString() }}
          </dd>
        </div>

        <div>
          <dt class="text-sm font-medium text-gray-500">Updated At</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(role.updatedAt).toLocaleString() }}
          </dd>
        </div>
      </dl>

      <div class="mt-6">
        <h3 class="text-lg font-semibold mb-3">Permissions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
          <div
            v-for="permission in role.permissions"
            :key="permission"
            class="p-3 border border-gray-200 rounded-lg bg-gray-50"
          >
            <p class="text-sm text-gray-900">{{ permission }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ArrowLeft as ArrowLeftIcon } from 'lucide-vue-next'
import { useRole } from '../composables/useRoles'

const route = useRoute()
const roleId = computed(() => parseInt(route.params.id as string))

const { data: role, isLoading, error } = useRole(roleId)

const systemRoles = ['super_admin', 'organization_admin', 'organization_user']

function isSystemRole(roleName: string): boolean {
  return systemRoles.includes(roleName)
}

function formatRoleName(name: string): string {
  return name.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}
</script>
