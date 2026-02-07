import axios, {
  type AxiosError,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios'
import { API_BASE_URL } from '../constants'
import { useAuthStore } from '@/features/auth/stores/authStore'
import { storeToRefs } from 'pinia'
import { router } from '@/router'

type RequestConfigWithRetry = InternalAxiosRequestConfig & {
  _retry?: boolean
}

type RefreshResponse = {
  accessToken: string
  expiresIn: number
}

const refreshClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,
})

let refreshPromise: Promise<string> | null = null

function isRefreshRequest(url?: string) {
  return !!url && url.includes('/auth/refresh')
}

async function refreshAccessToken() {
  if (!refreshPromise) {
    refreshPromise = (async () => {
      try {
        const authStore = useAuthStore()
        // Check if authStore is properly initialized
        if (!authStore) {
          throw new Error('Auth store is not initialized')
        }

        // Call refresh endpoint - cookie is automatically sent by browser
        const response = await refreshClient.post<RefreshResponse>(
          '/auth/refresh',
          undefined
        )
        const { accessToken: newToken, expiresIn } = response.data
        authStore.updateAccessToken(newToken, expiresIn)
        return newToken
      } catch (error) {
        // Reset refresh promise on error to allow retry
        refreshPromise = null
        throw error
      }
    })()
  }

  try {
    return await refreshPromise
  } finally {
    refreshPromise = null
  }
}

function handleUnauthorized() {
  try {
    const authStore = useAuthStore()
    if (authStore) {
      authStore.logout()
    }
  } catch (error) {
    console.error('Error in handleUnauthorized:', error)
  }
  router.push('/login')
}

// Create axios instance
export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,
})

// Request interceptor - add auth token
apiClient.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    try {
      const authStore = useAuthStore()
      
      // Check if authStore is properly initialized
      if (!authStore) {
        console.warn('Auth store is not initialized, proceeding without token')
        return config
      }

      // Use storeToRefs to properly access the refs
      const { accessToken } = storeToRefs(authStore)

      // Check if token needs refresh before making the request
      if (!isRefreshRequest(config.url) && accessToken.value && authStore.shouldRefreshToken()) {
        try {
          await refreshAccessToken()
        } catch (error) {
          handleUnauthorized()
          return Promise.reject(error)
        }
      }

      const token = accessToken.value

      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`
      }

      return config
    } catch (error) {
      console.error('Error in request interceptor:', error)
      // Proceed with the request even if there's an an error with the auth store
      return config
    }
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle errors
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    return response
  },
  async (error: AxiosError) => {
    const status = error.response?.status
    const originalRequest = error.config as RequestConfigWithRetry | undefined

    if (status === 401) {
      try {
        const authStore = useAuthStore()
        
        // Check if authStore is properly initialized
        if (!authStore) {
          console.warn('Auth store is not initialized, redirecting to login')
          router.push('/login')
          return Promise.reject(error)
        }
        
        // Use storeToRefs to properly access the refs
        const { accessToken } = storeToRefs(authStore)
        const hasToken = !!accessToken.value

        if (hasToken && originalRequest && !originalRequest._retry && !isRefreshRequest(originalRequest.url)) {
          originalRequest._retry = true

          try {
            await refreshAccessToken()
            const newToken = accessToken.value

            if (newToken && originalRequest.headers) {
              originalRequest.headers.Authorization = `Bearer ${newToken}`
            }

            return apiClient(originalRequest)
          } catch (refreshError) {
            handleUnauthorized()
            return Promise.reject(refreshError)
          }
        }

        if (hasToken) {
          handleUnauthorized()
        }

        return Promise.reject(error)
      } catch (storeError) {
        console.error('Error accessing auth store in response interceptor:', storeError)
        router.push('/login')
        return Promise.reject(error)
      }
    }

    if (status === 403) {
      console.error('Forbidden: Insufficient permissions')
    }

    return Promise.reject(error)
  }
)
