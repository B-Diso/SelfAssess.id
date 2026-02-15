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

async function refreshAccessToken(): Promise<string> {
  if (!refreshPromise) {
    refreshPromise = (async () => {
      try {
        const authStore = useAuthStore()

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

  return refreshPromise
}

function handleUnauthorized() {
  const authStore = useAuthStore()
  authStore.logout()

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
    const authStore = useAuthStore()

    // Use storeToRefs to properly access the refs
    const { accessToken } = storeToRefs(authStore)

      // Check if token needs refresh before making the request
      if (!isRefreshRequest(config.url) && accessToken.value && authStore.shouldRefreshToken()) {
        try {
          const newToken = await refreshAccessToken()
          config.headers.Authorization = `Bearer ${newToken}`
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
      const authStore = useAuthStore()
      // Use storeToRefs to properly access the refs
      const { accessToken } = storeToRefs(authStore)
      const hasToken = !!accessToken.value

      if (hasToken && originalRequest && !originalRequest._retry && !isRefreshRequest(originalRequest.url)) {
        originalRequest._retry = true

        try {
          const newToken = await refreshAccessToken()

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
    }

    if (status === 403) {
      console.error('Forbidden: Insufficient permissions')
    }

    return Promise.reject(error)
  }
)
