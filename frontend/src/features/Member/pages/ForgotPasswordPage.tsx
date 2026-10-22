import { useState, type FormEvent } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { usePasswordReset } from "../hooks/usePasswordReset";

export function ForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const { status, message, requestLink } = usePasswordReset();
  const isLoading = status === "loading";

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (email.trim() === "") return;
    void requestLink(email);
  };

  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-6 rounded-xl bg-background p-8 shadow-sm ring-1 ring-foreground/10">
        <header className="space-y-1 text-center">
          <h1 className="text-2xl font-semibold tracking-tight">
            Forgot password?
          </h1>
          <p className="text-sm text-muted-foreground">
            Enter your email and we will send you a reset link.
          </p>
        </header>

        {status === "success" ? (
          <p role="status" className="text-center text-sm">
            {message}
          </p>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            {status === "error" && (
              <p role="alert" className="text-sm text-destructive">
                {message}
              </p>
            )}

            <div className="space-y-1.5">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                autoComplete="email"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                disabled={isLoading}
              />
            </div>

            <Button type="submit" className="w-full" disabled={isLoading}>
              {isLoading ? "Sending…" : "Send reset link"}
            </Button>
          </form>
        )}

        <p className="text-center text-sm">
          <a href="/login" className="underline">
            Back to log in
          </a>
        </p>
      </section>
    </main>
  );
}
