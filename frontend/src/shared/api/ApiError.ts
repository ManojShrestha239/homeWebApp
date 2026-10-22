import type { ValidationErrors } from '@/shared/types/apiResponse'

/**
 * A typed error for anything that went wrong with an API call.
 * Components can check `error instanceof ApiError` and read the details safely.
 */
export class ApiError extends Error {
  readonly code: number                  // HTTP status (0 means no response at all)
  readonly type: string                  // machine label from the server
  readonly description: string | null
  readonly fieldErrors: ValidationErrors // per-field messages for forms

  constructor(args: {
    code: number
    type: string
    message: string
    description: string | null
    fieldErrors?: ValidationErrors
  }) {
    super(args.message)                  // sets the human-readable message
    this.name = 'ApiError'
    this.code = args.code
    this.type = args.type
    this.description = args.description
    this.fieldErrors = args.fieldErrors ?? {}   // default to an empty object
  }
}