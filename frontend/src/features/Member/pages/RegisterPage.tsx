import { RegisterForm } from '../components/RegisterForm'

export function RegisterPage() {
  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-6 rounded-xl bg-background p-8 shadow-sm ring-1 ring-foreground/10">
        <header className="space-y-1 text-center">
          <h1 className="text-2xl font-semibold tracking-tight">Create your account</h1>
          <p className="text-sm text-muted-foreground">Join the Himalayan Book Club in under a minute.</p>
        </header>

        <RegisterForm />
      </section>
    </main>
  )
}