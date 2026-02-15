import { createApp } from 'vue'
import { VueQueryPlugin } from '@tanstack/vue-query'
import App from './App.vue'
import './style.css'
import { createPinia } from 'pinia'
import { queryClient } from './lib/api/queryClient'
import 'vue-sonner/style.css'
import { registerPermissionDirectives } from './directives'

const app = createApp(App)

// Create and install pinia first
const pinia = createPinia()
app.use(pinia)

// Register permission directives
registerPermissionDirectives(app)

// Now we can safely initialize stores
import { useAuthStore } from './features/auth/stores/authStore'
import { useUserStore } from './features/auth/stores/userStore'

// Initialize stores
const authStore = useAuthStore()
const userStore = useUserStore()

// Rehydrate auth store tokens from localStorage
authStore.rehydrate()

// Initialize user store (will rehydrate from localStorage and fetch if needed)
userStore.initialize()

// Now install router (after stores are initialized)
import { router } from './router'
app.use(router)

// Install Vue Query plugin
app.use(VueQueryPlugin, { queryClient })

// Mount the app
app.mount('#app')
