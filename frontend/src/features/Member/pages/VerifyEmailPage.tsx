import { useVerifyEmail } from '../hooks/useVerifyEmail'

interface VerifyEmailPageProps {
  token: string | null   // read from the URL in App.tsx
}

export function VerifyEmailPage({ token }: VerifyEmailPageProps) {
  const { status, message } = useVerifyEmail(token)

  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-4 rounded-xl bg-background p-8 text-center shadow-sm ring-1 ring-foreground/10">
        <h1 className="text-2xl font-semibold tracking-tight">Email verification</h1>

        {/* role="status" lets screen readers announce the change */}
        <p role="status" className={status === 'error' ? 'text-destructive' : 'text-muted-foreground'}>
          {message}
        </p>

        {status === 'success' && (
          <a href="/" className="inline-block text-sm underline">
            Continue
          </a>
        )}

        {status === 'error' && (
          <a href="/" className="inline-block text-sm underline">
            Back to registration
          </a>
        )}
      </section>
    </main>
  )
}