import type { RegisterFormErrors, RegisterFormValues } from '../types/member.types'

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

/**
 * Checks the form in the browser BEFORE sending it. This gives instant feedback.
 * The backend still checks everything again, because the browser can be tricked.
 */
export function validateRegisterForm(values: RegisterFormValues): RegisterFormErrors {
  const errors: RegisterFormErrors = {}

  if (values.fullName.trim().length < 2) {
    errors.fullName = 'Name must be at least 2 characters.'
  }

  if (!EMAIL_PATTERN.test(values.email.trim())) {
    errors.email = 'Please enter a valid email address.'
  }

  if (values.password.length < 8) {
    errors.password = 'Password must be at least 8 characters.'
  } else if (!/[A-Z]/.test(values.password) || !/[a-z]/.test(values.password) || !/\d/.test(values.password)) {
    errors.password = 'Use upper and lower case letters plus a number.'
  }

  if (values.confirmPassword !== values.password) {
    errors.confirmPassword = 'Passwords do not match.'
  }

  return errors
}