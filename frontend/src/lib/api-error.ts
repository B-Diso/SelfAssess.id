import { isAxiosError } from 'axios'

interface ApiErrorResponse {
  message?: string
  errors?: Record<string, string[]>
}

export function getApiErrorMessage(error: unknown, fallbackMessage: string): string {
  if (isAxiosError(error)) {
    const data = error.response?.data as ApiErrorResponse | undefined

    if (data) {
      if (typeof data.message === 'string' && data.message.trim().length > 0) {
        return data.message
      }

      const errors = data.errors
      if (errors) {
        const firstKey = Object.keys(errors)[0]
        if (firstKey) {
          const firstMessage = errors[firstKey]?.[0]
          if (firstMessage) {
            return firstMessage
          }
        }
      }
    }
  }

  if (error instanceof Error && error.message) {
    return error.message
  }

  return fallbackMessage
}
