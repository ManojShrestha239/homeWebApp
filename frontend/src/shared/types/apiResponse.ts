// Mirrors the backend's MANDATORY JSON schema exactly, field for field
export interface ApiMeta {
  next_cursor: string | null     // for paginated lists later
  prev_cursor: string | null
}

export interface ApiResponse<TData> {
  code: number                   // the HTTP status, e.g. 201
  status: boolean                // true for 2xx, false for errors
  type: string                   // machine label, e.g. "MEMBER_REGISTERED"
  message: string               // sentence for the user
  description: string | null
  data: TData | null             // the payload
  meta: ApiMeta
}

// On 422, data looks like: { errors: { email: ["The email field is required."] } }
export type ValidationErrors = Record<string, string[]>