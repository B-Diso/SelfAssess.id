import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { ACCESS_TOKEN_KEY, ACCESS_TOKEN_EXPIRY_KEY, TOKEN_REFRESH_THRESHOLD_MS } from '@/lib/constants'

const REMEMBER_ME_KEY = 'remember_me'
const SESSION_INFO_KEY = 'session_info'

// Storage helpers - use localStorage for remember me, sessionStorage for non-remember me
function getStorage(isRememberMe: boolean) {
  return isRememberMe ? localStorage : sessionStorage
}

interface SessionInfo {
  duration: number
  loginTime: number
}

export const useAuthStore = defineStore('auth', () => {
  const accessToken = ref<string | null>(null)
  const tokenExpiresAt = ref<number | null>(null)
  const rememberMe = ref<boolean>(true)
  const sessionInfo = ref<SessionInfo | null>(null)

  const initializeTokens = () => {
    // Load remember me preference from localStorage (persisted)
    const storedRememberMe = localStorage.getItem(REMEMBER_ME_KEY)
    if (storedRememberMe !== null) {
      rememberMe.value = storedRememberMe === 'true'
    }

    const storage = getStorage(rememberMe.value)

    // Load access token
    const storedToken = storage.getItem(ACCESS_TOKEN_KEY)
    if (storedToken) {
      accessToken.value = storedToken
    }

    // Load token expiry
    const storedExpiry = storage.getItem(ACCESS_TOKEN_EXPIRY_KEY)
    if (storedExpiry) {
      const parsedExpiry = Number(storedExpiry)
      if (!Number.isNaN(parsedExpiry)) {
        tokenExpiresAt.value = parsedExpiry
      }
    }

    // Load session info
    const storedSessionInfo = storage.getItem(SESSION_INFO_KEY)
    if (storedSessionInfo) {
      try {
        sessionInfo.value = JSON.parse(storedSessionInfo)
      } catch {
        sessionInfo.value = null
      }
    }
  }

  initializeTokens()

  const isAuthenticated = computed(() => !!accessToken.value)

  /**
   * Get the session duration in milliseconds
   */
  const getSessionDuration = computed(() => {
    return sessionInfo.value?.duration ?? null
  })

  /**
   * Get the remaining session time in milliseconds
   * Returns null if no session or session has expired
   */
  const getSessionRemaining = computed(() => {
    if (!tokenExpiresAt.value) return null
    const remaining = tokenExpiresAt.value - Date.now()
    return remaining > 0 ? remaining : 0
  })

  function setTokens(token: string, expiresIn: number, isRememberMe: boolean = true) {
    accessToken.value = token
    rememberMe.value = isRememberMe

    const storage = getStorage(isRememberMe)

    // Store access token
    storage.setItem(ACCESS_TOKEN_KEY, token)

    // Store remember me preference in localStorage (always persist this)
    localStorage.setItem(REMEMBER_ME_KEY, String(isRememberMe))

    // Calculate and store expiry
    const expiresAt = Date.now() + expiresIn * 1000
    tokenExpiresAt.value = expiresAt
    storage.setItem(ACCESS_TOKEN_EXPIRY_KEY, expiresAt.toString())

    // Store session info
    const info: SessionInfo = {
      duration: expiresIn * 1000,
      loginTime: Date.now(),
    }
    sessionInfo.value = info
    storage.setItem(SESSION_INFO_KEY, JSON.stringify(info))
  }

  function updateAccessToken(token: string, expiresIn: number) {
    accessToken.value = token
    const storage = getStorage(rememberMe.value)
    storage.setItem(ACCESS_TOKEN_KEY, token)
    const expiresAt = Date.now() + expiresIn * 1000
    tokenExpiresAt.value = expiresAt
    storage.setItem(ACCESS_TOKEN_EXPIRY_KEY, expiresAt.toString())

    // Update session info
    if (sessionInfo.value) {
      sessionInfo.value = {
        ...sessionInfo.value,
        duration: expiresIn * 1000,
      }
      storage.setItem(SESSION_INFO_KEY, JSON.stringify(sessionInfo.value))
    }
  }

  function getTimeUntilExpiry() {
    if (!tokenExpiresAt.value) return null
    return tokenExpiresAt.value - Date.now()
  }

  function shouldRefreshToken(bufferMs = TOKEN_REFRESH_THRESHOLD_MS) {
    const timeLeft = getTimeUntilExpiry()
    if (timeLeft === null) return false
    return timeLeft <= bufferMs
  }

  function logout() {
    accessToken.value = null
    tokenExpiresAt.value = null
    sessionInfo.value = null
    // Note: rememberMe is not cleared on logout - user preference persists
    
    // Clear from both storages to be safe
    sessionStorage.removeItem(ACCESS_TOKEN_KEY)
    sessionStorage.removeItem(ACCESS_TOKEN_EXPIRY_KEY)
    sessionStorage.removeItem(SESSION_INFO_KEY)
    localStorage.removeItem(ACCESS_TOKEN_KEY)
    localStorage.removeItem(ACCESS_TOKEN_EXPIRY_KEY)
    localStorage.removeItem(SESSION_INFO_KEY)
  }

  function rehydrate() {
    initializeTokens()
  }

  /**
   * Update the remember me preference
   */
  function setRememberMe(value: boolean) {
    rememberMe.value = value
    localStorage.setItem(REMEMBER_ME_KEY, String(value))
  }

  return {
    accessToken,
    tokenExpiresAt,
    rememberMe,
    sessionInfo,
    isAuthenticated,
    getSessionDuration,
    getSessionRemaining,
    setTokens,
    updateAccessToken,
    shouldRefreshToken,
    logout,
    rehydrate,
    setRememberMe,
  }
})
