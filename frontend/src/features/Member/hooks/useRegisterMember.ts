import { useCallback, useRef, useState } from 'react'
import { ApiError } from '@/shared/api/ApiError'
import { registerMember } from '../api/memberApi'
import type { Member, RegisterFormValues } from '../types/member.types'

type RegisterStatus = 'idle' | 'loading' | 'success' | 'error'

interface UseRegisterMemberResult {
  status: RegisterStatus
  member: Member | null
  successMessage: string | null
  errorMessage: string | null
  fieldErrors: Record<string, string[]>
  submit: (values: RegisterFormValues) => Promise<void>
}

/**
 * Keeps all the "loading / success / error" logic in one place,
 * so the form component only has to display it.
 */
export function useRegisterMember(): UseRegisterMemberResult {
  const [status, setStatus] = useState<RegisterStatus>('idle')
  const [member, setMember] = useState<Member | null>(null)
  const [successMessage, setSuccessMessage] = useState<string | null>(null)
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  // A "ref" remembers the last request without causing a re-render
  const abortRef = useRef<AbortController | null>(null)

  const submit = useCallback(async (values: RegisterFormValues): Promise<void> => {
    abortRef.current?.abort()                 // cancel any older request still running
    const controller = new AbortController()
    abortRef.current = controller

    // Reset the screen for a fresh attempt
    setStatus('loading')
    setErrorMessage(null)
    setFieldErrors({})

    try {
      const response = await registerMember(values, controller.signal)
      setMember(response.data)
      setSuccessMessage(response.message)
      setStatus('success')
    } catch (error: unknown) {
      if (controller.signal.aborted) return   // this request was cancelled on purpose, so ignore it

      if (error instanceof ApiError) {
        setErrorMessage(error.message)
        setFieldErrors(error.fieldErrors)     // e.g. { email: ["..."] } from a 422
      } else {
        setErrorMessage('Something went wrong. Please try again.')
      }
      setStatus('error')
    }
  }, [])

  return { status, member, successMessage, errorMessage, fieldErrors, submit }
}