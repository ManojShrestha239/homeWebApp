import { useEffect, useRef, useState } from 'react'
import { ApiError } from '@/shared/api/ApiError'
import { verifyMemberEmail } from '../api/memberApi'

export type VerifyStatus = 'loading' | 'success' | 'error'

interface UseVerifyEmailResult {
  status: VerifyStatus
  message: string
}

/**
 * Calls the verify API once when the page opens.
 * A missing token is shown as an error right away, without calling the server.
 */
export function useVerifyEmail(token: string | null): UseVerifyEmailResult {
  // Start in the right state. This avoids calling setState inside the effect
  const [status, setStatus] = useState<VerifyStatus>(token ? 'loading' : 'error')
  const [message, setMessage] = useState<string>(
    token ? 'Verifying your email…' : 'This verification link is incomplete.',
  )

  // A ref remembers that we already sent the request.
  // React's StrictMode runs effects twice in development, and a second call would fail,
  // because the first call already did the work.
  const startedRef = useRef(false)

  useEffect(() => {
    if (!token || startedRef.current) return
    startedRef.current = true

    verifyMemberEmail(token)
      .then((response) => {
        setStatus('success')
        setMessage(response.message)
      })
      .catch((error: unknown) => {
        setStatus('error')
        setMessage(
          error instanceof ApiError ? error.message : 'Something went wrong. Please try again.',
        )
      })
  }, [token])

  return { status, message }
}