const TOKEN_KEY = "hbc_access_token";
const REMEMBER_KEY = "hbc_remember";

/** Remembered logins live in localStorage; normal logins live in sessionStorage */
function activeStore(): Storage {
  try {
    return localStorage.getItem(REMEMBER_KEY) === "1"
      ? localStorage
      : sessionStorage;
  } catch {
    return sessionStorage;
  }
}

export const tokenStorage = {
  get(): string | null {
    try {
      // Check both, so a token saved either way is found
      return (
        localStorage.getItem(TOKEN_KEY) ?? sessionStorage.getItem(TOKEN_KEY)
      );
    } catch {
      return null;
    }
  },

  set(token: string, remember: boolean): void {
    try {
      // Clear the other store first, so only one copy exists
      this.clear();
      localStorage.setItem(REMEMBER_KEY, remember ? "1" : "0");
      activeStore().setItem(TOKEN_KEY, token);
    } catch {
      // Storage blocked: the user stays logged out after a refresh
    }
  },

  clear(): void {
    try {
      localStorage.removeItem(TOKEN_KEY);
      sessionStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(REMEMBER_KEY);
    } catch {
      // Nothing to clear if storage is blocked
    }
  },
};
