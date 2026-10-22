const TOKEN_KEY = 'hbc_access_token'   // "hbc" = Himalayan Book Club, a unique key name

export const tokenStorage = {
  get(): string | null {
    try {
      return localStorage.getItem(TOKEN_KEY)
    } catch {
      return null            // storage can be blocked (private mode, some browser settings)
    }
  },

  set(token: string): void {
    try {
      localStorage.setItem(TOKEN_KEY, token)
    } catch {
      // Storage blocked: the user simply stays logged out after a refresh
    }
  },

  clear(): void {
    try {
      localStorage.removeItem(TOKEN_KEY)
    } catch {
      // Nothing to clear if storage is blocked
    }
  },
}