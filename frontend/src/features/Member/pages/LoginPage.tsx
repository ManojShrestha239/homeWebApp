import { LoginForm } from "../components/LoginForm";

export function LoginPage() {
  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-6 rounded-xl bg-background p-8 shadow-sm ring-1 ring-foreground/10">
        <header className="space-y-1 text-center">
          <h1 className="text-2xl font-semibold tracking-tight">Log in</h1>
          <p className="text-sm text-muted-foreground">
            Welcome back to the Himalayan Book Club.
          </p>
        </header>

        <LoginForm />

        <p className="text-center text-sm">
          New here?{" "}
          <a href="/register" className="underline">
            Create an account
          </a>
        </p>
        <p className="text-center text-sm">
          <a href="/forgot-password" className="underline">
            Forgot password?
          </a>
        </p>
      </section>
    </main>
  );
}
