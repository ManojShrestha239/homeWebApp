import { useState, type ChangeEvent, type FormEvent } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useLogin } from '../hooks/useLogin'
import type { LoginFormValues } from '../types/member.types'

const INITIAL_VALUES: LoginFormValues = {
  email: '',
  password: '',
}

export function LoginForm() {
  const [values, setValues] = useState<LoginFormValues>(INITIAL_VALUES)
  const [clientError, setClientError] = useState<string | null>(null)
  const { status, member, errorMessage, submit, logout } = useLogin()

  const isLoading = status === 'loading'

  const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = event.target
    setValues((previous) => ({ ...previous, [name]: value }))
  }

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()   // stop the page from reloading

    // Quick check in the browser so we don't send an empty form
    if (values.email.trim() === '' || values.password === '') {
      setClientError('Please enter your email and password.')
      return
    }

    setClientError(null)
    await submit(values)
  }

  // ── Logged-in view ─────────────────────────────────────
  if (status === 'success' && member) {
    return (
      <div role="status" className="space-y-4 text-center">
        <h2 className="text-lg font-semibold">Welcome back, {member.full_name}!</h2>
        <p className="text-sm text-muted-foreground">You are now logged in.</p>
        <Button type="button" variant="outline" className="w-full" onClick={logout}>
          Log out
        </Button>
      </div>
    )
  }

  // ── The form ───────────────────────────────────────────
  const shownError = clientError ?? errorMessage

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      {shownError && (
        <div role="alert" className="rounded-md border border-destructive/40 bg-destructive/10 p-3 text-sm text-destructive">
          {shownError}
        </div>
      )}

      <div className="space-y-1.5">
        <Label htmlFor="email">Email</Label>
        <Input
          id="email"
          name="email"
          type="email"
          autoComplete="email"
          value={values.email}
          onChange={handleChange}
          disabled={isLoading}
        />
      </div>

      <div className="space-y-1.5">
        <Label htmlFor="password">Password</Label>
        <Input
          id="password"
          name="password"
          type="password"
          autoComplete="current-password"
          value={values.password}
          onChange={handleChange}
          disabled={isLoading}
        />
      </div>

      <Button type="submit" className="w-full" disabled={isLoading}>
        {isLoading ? 'Logging in…' : 'Log in'}
      </Button>
    </form>
  )
}