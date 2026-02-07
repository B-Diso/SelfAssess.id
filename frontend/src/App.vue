<template>
  <component :is="layout">
    <RouterView />
  </component>
  <Toaster position="top-right" :duration="3000" rich-colors close-button />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/authStore'
import AppLayout from '@/layouts/AppLayout.vue'
import { Toaster } from '@/components/ui/sonner'

const route = useRoute()
const authStore = useAuthStore()

// Determine which layout to use based on route and auth state
const layout = computed(() => {
  // If user is not authenticated or on login page, don't use any layout
  // (LoginPage has its own AuthLayout)
  if (!authStore.isAuthenticated || route.path === '/login') {
    return 'div'
  }

  // Otherwise use AppLayout
  return AppLayout
})
</script>
