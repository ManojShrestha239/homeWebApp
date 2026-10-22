import { useCallback, useRef, useState } from "react";
import { ApiError } from "@/shared/api/ApiError";
import { requestPasswordReset, resetPassword } from "../api/memberApi";
import type { ResetPasswordValues } from "../types/member.types";

type ResetStatus = "idle" | "loading" | "success" | "error";

/** Handles both steps: asking for the email, and setting the new password */
export function usePasswordReset() {
  const [status, setStatus] = useState<ResetStatus>("idle");
  const [message, setMessage] = useState<string | null>(null);
  const abortRef = useRef<AbortController | null>(null);

  const run = useCallback(
    async (task: (signal: AbortSignal) => Promise<{ message: string }>) => {
      abortRef.current?.abort();
      const controller = new AbortController();
      abortRef.current = controller;

      setStatus("loading");
      setMessage(null);

      try {
        const response = await task(controller.signal);
        setMessage(response.message);
        setStatus("success");
      } catch (error: unknown) {
        if (controller.signal.aborted) return;
        setMessage(
          error instanceof ApiError
            ? error.description
              ? `${error.message} ${error.description}`
              : error.message
            : "Something went wrong. Please try again.",
        );
        setStatus("error");
      }
    },
    [],
  );

  /** Step 1: ask for a reset email */
  const requestLink = useCallback(
    (email: string) => run((signal) => requestPasswordReset(email, signal)),
    [run],
  );

  /** Step 2: set the new password using the token from the email */
  const setNewPassword = useCallback(
    (token: string, values: ResetPasswordValues) =>
      run((signal) => resetPassword(token, values, signal)),
    [run],
  );

  return { status, message, requestLink, setNewPassword };
}
