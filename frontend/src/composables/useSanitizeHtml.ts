import DOMPurify from 'dompurify'

/**
 * Composable for sanitizing HTML content to prevent XSS attacks
 */
export function useSanitizeHtml() {
  /**
   * Sanitize HTML string with DOMPurify
   * Allows safe HTML tags like <p>, <ol>, <ul>, <li>, <strong>, <em>, etc.
   */
  function sanitize(dirty: string | null | undefined): string {
    if (!dirty) return ''
    
    return DOMPurify.sanitize(dirty, {
      ALLOWED_TAGS: [
        'p', 'br', 'strong', 'em', 'u', 'b', 'i',
        'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'code', 'pre',
        'a', 'span', 'div'
      ],
      ALLOWED_ATTR: ['href', 'target', 'rel', 'class'],
      ALLOW_DATA_ATTR: false,
    })
  }

  return {
    sanitize,
  }
}

