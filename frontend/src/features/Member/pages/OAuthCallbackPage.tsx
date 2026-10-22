import { useEffect, useRef, useState } from "react";
import { tokenStorage } from "@/shared/api/tokenStorage";
import { getCurrentMember } from "../api/memberApi";

interface CallbackState {
  status: "loading" | "success" | "error";
  message: string;
}

export function OAuthCallbackPage() {
  const [state, setState] = useState<CallbackState>({
    status: "loading",
    message: "Finishing your Google sign-in…",
  });

  // Stops React's StrictMode from running the handler twice.
  // The first run removes the token from the URL, so the second run would find nothing.
  const handledRef = useRef(false);

  useEffect(() => {
    if (handledRef.current) return;
    handledRef.current = true;

    // Read the values after the "#". The browser never sent these to any server
    const params = new URLSearchParams(window.location.hash.slice(1));

    // Remove the token from the address bar right away, so it isn't left in history
    window.history.replaceState(null, "", window.location.pathname);

    const errorMessage =
      params.get("message") ?? "Google sign-in failed. Please try again.";
    if (params.get("error")) {
      setState({ status: "error", message: errorMessage });
      return;
    }

    const token = params.get("token");
    if (!token) {
      setState({
        status: "error",
        message: "Sign-in details are missing. Please try again.",
      });
      return;
    }

    // Save the wristband, then ask the API who it belongs to
    tokenStorage.set(token);
    getCurrentMember()
      .then(() => {
        window.location.replace("/dashboard"); // replace, so the Back button skips the callback URL
      })
      .catch(() => {
        tokenStorage.clear(); // don't keep a token we couldn't verify
        setState({
          status: "error",
          message: "We could not load your account. Please try again.",
        });
      });
  }, []);

  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-4 rounded-xl bg-background p-8 text-center shadow-sm ring-1 ring-foreground/10">
        <h1 className="text-2xl font-semibold tracking-tight">
          Google sign-in
        </h1>

        <p
          role="status"
          className={
            state.status === "error"
              ? "text-destructive"
              : "text-muted-foreground"
          }
        >
          {state.message}
        </p>

        {state.status === "success" && (
          <a href="/" className="inline-block text-sm underline">
            Continue
          </a>
        )}

        {state.status === "error" && (
          <a href="/login" className="inline-block text-sm underline">
            Back to log in
          </a>
        )}
      </section>
    </main>
  );
}
