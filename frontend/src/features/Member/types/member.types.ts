// What the server sends back for a member (matches MemberResource in PHP)
export interface Member {
  id: number
  full_name: string
  email: string
  email_verified: boolean
  created_at: string | null
}

// The shape of the form inside the browser (camelCase, friendly for React)
export interface RegisterFormValues {
  fullName: string
  email: string
  password: string
  confirmPassword: string
}

// Error messages for each form field (empty = no error)
export type RegisterFormErrors = Partial<Record<keyof RegisterFormValues, string>>

// The exact shape the backend expects (snake_case)
export interface RegisterMemberPayload {
  full_name: string
  email: string
  password: string
  password_confirmation: string
}