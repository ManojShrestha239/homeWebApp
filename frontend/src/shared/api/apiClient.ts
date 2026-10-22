import type { ApiResponse, ValidationErrors } from '@/shared/types/apiResponse'
import { ApiError } from './ApiError'
import { tokenStorage } from './tokenStorage'

// Read the server address from .env. Falls back to localhost if it's missing
const BASE_URL: string = import.meta.env.VITE_API_BASE_URL ?? 'http://homewebapp.manojshrestha239.com.np/api/v1'

export interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  body?: unknown
  signal?: AbortSignal                    // lets us cancel the request (e.g. user left the page)
}

/**
 * One function for ALL API calls. It sends the request, checks the reply,
 * and throws a typed ApiError whenever something is wrong.
 */
export async function apiRequest<TData>(
  path: string,
  options: RequestOptions = {},
): Promise<ApiResponse<TData>> {
  // ── 1. Send the request ───────────────────────────────
  let response: Response
  try {
    // Attach the wristband to every request, if we have one
    const token = tokenStorage.get()
    
    response = await fetch(`${BASE_URL}${path}`, {
      method: options.method ?? 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),   // only added when logged in
      },
      body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
      signal: options.signal,
    })
  } catch {
    // No response at all: server is off, offline, or CORS blocked it
    throw new ApiError({
      code: 0,
      type: 'NETWORK_ERROR',
      message: 'Cannot reach the server. Check your connection.',
      description: null,
    })
  }

  // ── 2. Read the reply as unknown (we don't trust it yet) ─
  const raw: unknown = await response.json().catch(() => null)

  if (!isApiResponse(raw)) {
    throw new ApiError({
      code: response.status,
      type: 'INVALID_RESPONSE',
      message: 'The server sent an unexpected response.',
      description: null,
    })
  }

  // ── 3. Treat status:false as an error ──────────────────
  const typed = raw as ApiResponse<TData>
  if (!typed.status) {
    throw new ApiError({
      code: typed.code,
      type: typed.type,
      message: typed.message,
      description: typed.description,
      fieldErrors: extractFieldErrors(typed.data),
    })
  }

  return typed
}

/** Runtime check: is this object shaped like our schema? */
function isApiResponse(value: unknown): value is ApiResponse<unknown> {
  return (
    typeof value === 'object' &&
    value !== null &&
    'status' in value &&
    typeof value.status === 'boolean' &&
    'code' in value &&
    typeof value.code === 'number'
  )
}

/** Pulls data.errors out of a 422 reply, or returns an empty object */
function extractFieldErrors(data: unknown): ValidationErrors {
  if (typeof data === 'object' && data !== null && 'errors' in data && typeof data.errors === 'object' && data.errors !== null) {
    return data.errors as ValidationErrors
  }
  return {}
}