import { useState, type ChangeEvent, type FormEvent } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useRegisterMember } from '../hooks/useRegisterMember'
import type { RegisterFormErrors, RegisterFormValues } from '../types/member.types'
import { validateRegisterForm } from '../utils/validateRegisterForm'

const INITIAL_VALUES: RegisterFormValues = {
  fullName: '',
  email: '',
  password: '',
  confirmPassword: '',
}

// Maps each form field to the name the backend uses in its 422 errors
const SERVER_FIELD: Record<keyof RegisterFormValues, string> = {
  fullName: 'full_name',
  email: 'email',
  password: 'password',
  confirmPassword: 'password_confirmation',
}

export function RegisterForm() {
  const [values, setValues] = useState<RegisterFormValues>(INITIAL_VALUES)
  const [clientErrors, setClientErrors] = useState<RegisterFormErrors>({})
  const { status, successMessage, errorMessage, fieldErrors, submit } = useRegisterMember()

  const isLoading = status === 'loading'

  // One handler for every input. The input's `name` must match a key in RegisterFormValues
  const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = event.target
    setValues((previous) => ({ ...previous, [name]: value }))
  }

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()                    // stop the browser from reloading the page

    const errors = validateRegisterForm(values)
    setClientErrors(errors)
    if (Object.keys(errors).length > 0) return   // bad data: don't bother the server

    await submit(values)
  }

  // Show a browser-side error first, otherwise the server's error for that field
  const errorFor = (field: keyof RegisterFormValues): string | undefined =>
    clientErrors[field] ?? fieldErrors[SERVER_FIELD[field]]?.[0]

  // ── Success screen ─────────────────────────────────────
  if (status === 'success') {
    return (
      <div role="status" className="space-y-2 text-center">
        <h2 className="text-lg font-semibold">Check your inbox</h2>
        <p className="text-sm text-muted-foreground">{successMessage}</p>
      </div>
    )
  }

  // ── The form ───────────────────────────────────────────
  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      {errorMessage && (
        <div role="alert" className="rounded-md border border-destructive/40 bg-destructive/10 p-3 text-sm text-destructive">
          {errorMessage}
        </div>
      )}

      <Field id="fullName" label="Full name" value={values.fullName} onChange={handleChange}
        error={errorFor('fullName')} disabled={isLoading} autoComplete="name" />

      <Field id="email" label="Email" type="email" value={values.email} onChange={handleChange}
        error={errorFor('email')} disabled={isLoading} autoComplete="email" />

      <Field id="password" label="Password" type="password" value={values.password} onChange={handleChange}
        error={errorFor('password')} disabled={isLoading} autoComplete="new-password" />

      <Field id="confirmPassword" label="Confirm password" type="password" value={values.confirmPassword}
        onChange={handleChange} error={errorFor('confirmPassword')} disabled={isLoading} autoComplete="new-password" />

      <Button type="submit" className="w-full" disabled={isLoading}>
        {isLoading ? 'Creating account…' : 'Create account'}
      </Button>
    </form>
  )
}

// ── A small helper component: label + input + error text, all in one ───
interface FieldProps {
  id: keyof RegisterFormValues
  label: string
  type?: string
  value: string
  onChange: (event: ChangeEvent<HTMLInputElement>) => void
  error?: string
  disabled: boolean
  autoComplete?: string
}

function Field({ id, label, error, ...inputProps }: FieldProps) {
  const errorId = `${id}-error`

  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>{label}</Label>
      <Input
        id={id}
        name={id}                                       // must match the form value key
        aria-invalid={Boolean(error)}                   // screen readers announce the problem
        aria-describedby={error ? errorId : undefined}
        {...inputProps}
      />
      {error && <p id={errorId} className="text-sm text-destructive">{error}</p>}
    </div>
  )
}