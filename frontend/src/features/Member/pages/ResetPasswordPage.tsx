import { useState, type FormEvent } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { usePasswordReset } from "../hooks/usePasswordReset";
import type { ResetPasswordValues } from "../types/member.types";

interface ResetPasswordPageProps {
  token: string;
}

const INITIAL: ResetPasswordValues = { password: "", confirmPassword: "" };

export function ResetPasswordPage({ token }: ResetPasswordPageProps) {
  const [values, setValues] = useState<ResetPasswordValues>(INITIAL);
  const [clientError, setClientError] = useState<string | null>(null);
  const { status, message, setNewPassword } = usePasswordReset();
  const isLoading = status === "loading";

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    // Quick checks in the browser. The server checks again.
    if (values.password.length < 8) {
      setClientError("Password must be at least 8 characters.");
      return;
    }
    if (values.password !== values.confirmPassword) {
      setClientError("Passwords do not match.");
      return;
    }

    setClientError(null);
    void setNewPassword(token, values);
  };

  const shownError = clientError ?? (status === "error" ? message : null);

  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-6 rounded-xl bg-background p-8 shadow-sm ring-1 ring-foreground/10">
        <header className="space-y-1 text-center">
          <h1 className="text-2xl font-semibold tracking-tight">
            Choose a new password
          </h1>
        </header>

        {status === "success" ? (
          <div className="space-y-4 text-center">
            <p role="status" className="text-sm">
              {message}
            </p>
            <a href="/login" className="inline-block text-sm underline">
              Go to log in
            </a>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            {shownError && (
              <p role="alert" className="text-sm text-destructive">
                {shownError}
              </p>
            )}

            <div className="space-y-1.5">
              <Label htmlFor="password">New password</Label>
              <Input
                id="password"
                type="password"
                autoComplete="new-password"
                value={values.password}
                onChange={(e) =>
                  setValues((v) => ({ ...v, password: e.target.value }))
                }
                disabled={isLoading}
              />
              <p className="text-xs text-muted-foreground">
                At least 8 characters, with upper and lower case letters and a
                number.
              </p>
            </div>

            <div className="space-y-1.5">
              <Label htmlFor="confirmPassword">Confirm new password</Label>
              <Input
                id="confirmPassword"
                type="password"
                autoComplete="new-password"
                value={values.confirmPassword}
                onChange={(e) =>
                  setValues((v) => ({ ...v, confirmPassword: e.target.value }))
                }
                disabled={isLoading}
              />
            </div>

            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? "Saving…" : "Set new password"}
            </Button>
          </form>
        )}
      </section>
    </main>
  );
}
