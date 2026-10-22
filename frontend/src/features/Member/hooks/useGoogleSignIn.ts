import { useCallback, useState } from "react";
import { ApiError } from "@/shared/api/ApiError";
import { getGoogleAuthUrl } from "../api/memberApi";

interface UseGoogleSignInResult {
  isStarting: boolean;
  errorMessage: string | null;
  start: () => Promise<void>;
}

/**
 * Gets Google's sign-in URL from our API, then sends the browser there.
 * On success the page leaves, so the loading state only matters for failures.
 */
export function useGoogleSignIn(): UseGoogleSignInResult {
  const [isStarting, setIsStarting] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const start = useCallback(async (remember: boolean): Promise<void> => {
    setIsStarting(true);
    setErrorMessage(null);

    try {
      const response = await getGoogleAuthUrl(remember);
      const url = response.data?.url;
      if (!url) throw new Error("Missing Google sign-in address.");
      window.location.assign(url);
    } catch (error: unknown) {
      setErrorMessage(
        error instanceof ApiError
          ? error.message
          : "Could not start Google sign-in. Please try again.",
      );
      setIsStarting(false);
    }
  }, []);

  return { isStarting, errorMessage, start };
}
