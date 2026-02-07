// src/stores/index.ts
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

export const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

// Export stores for easy access throughout the application
export { useAuthStore } from '@/features/auth/stores/authStore';
export { useUserStore } from '@/features/auth/stores/userStore';
