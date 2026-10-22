import { useCallback, useRef, useState } from "react";
import { ApiError } from "@/shared/api/ApiError";
import { tokenStorage } from "@/shared/api/tokenStorage";
import { loginMember } from "../api/memberApi";
import type { LoginFormValues, Member } from "../types/member.types";

type LoginStatus = "idle" | "loading" | "success" | "error";

interface UseLoginResult {
  status: LoginStatus;
  member: Member | null;
  errorMessage: string | null;
  submit: (values: LoginFormValues) => Promise<void>;
  logout: () => void;
}

/**
 * Handles the login request, saves the token on success, and clears it on logout.
 */
export function useLogin(): UseLoginResult {
  const [status, setStatus] = useState<LoginStatus>("idle");
  const [member, setMember] = useState<Member | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  // Cancels an older login request if the user submits again
  const abortRef = useRef<AbortController | null>(null);

  const submit = useCallback(async (values: LoginFormValues): Promise<void> => {
    abortRef.current?.abort();
    const controller = new AbortController();
    abortRef.current = controller;

    setStatus("loading");
    setErrorMessage(null);

    try {
      const response = await loginMember(values, controller.signal);

      // Save the wristband so later requests can use it
      tokenStorage.set(response.data?.access_token ?? "", values.remember);
      setMember(response.data?.member ?? null);
      setStatus("success");

      window.location.assign("/dashboard"); // same destination as Google login
      return;
    } catch (error: unknown) {
      if (controller.signal.aborted) return; // cancelled on purpose, so ignore it

      if (error instanceof ApiError) {
        // Show the main message, plus the extra hint from the server if there is one
        setErrorMessage(
          error.description
            ? `${error.message} ${error.description}`
            : error.message,
        );
      } else {
        setErrorMessage("Something went wrong. Please try again.");
      }
      setStatus("error");
    }
  }, []);

  const logout = useCallback((): void => {
    tokenStorage.clear(); // JWTs are stateless, so "logout" means forgetting the token
    setMember(null);
    setStatus("idle");
  }, []);

  return { status, member, errorMessage, submit, logout };
}
