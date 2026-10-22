import { apiRequest } from '@/shared/api/apiClient'
import type { ApiResponse } from '@/shared/types/apiResponse'
import type { Member, RegisterFormValues, RegisterMemberPayload } from '../types/member.types'

/** Translates the form's camelCase values into the backend's snake_case contract */
function toRegisterPayload(values: RegisterFormValues): RegisterMemberPayload {
  return {
    full_name: values.fullName.trim(),
    email: values.email.trim(),
    password: values.password,
    password_confirmation: values.confirmPassword,
  }
}

/** The ONLY function that knows the registration URL */
export function registerMember(
  values: RegisterFormValues,
  signal?: AbortSignal,
): Promise<ApiResponse<Member>> {
  return apiRequest<Member>('/members/register', {
    method: 'POST',
    body: toRegisterPayload(values),
    signal,
  })
}