import { apiRequest } from "@/shared/api/apiClient";
import type { ApiResponse } from "@/shared/types/apiResponse";
import type {
  Member,
  RegisterFormValues,
  RegisterMemberPayload,
  ResetPasswordValues,
} from "../types/member.types";
import type {
  LoginFormValues,
  LoginMemberPayload,
  LoginData,
} from "../types/member.types";
import type { GoogleAuthUrlData } from "../types/member.types";

/** Translates the form's camelCase values into the backend's snake_case contract */
function toRegisterPayload(values: RegisterFormValues): RegisterMemberPayload {
  return {
    full_name: values.fullName.trim(),
    email: values.email.trim(),
    password: values.password,
    password_confirmation: values.confirmPassword,
  };
}

/** The ONLY function that knows the registration URL */
export function registerMember(
  values: RegisterFormValues,
  signal?: AbortSignal,
): Promise<ApiResponse<Member>> {
  return apiRequest<Member>("/members/register", {
    method: "POST",
    body: toRegisterPayload(values),
    signal,
  });
}

/** Sends the token from the email link to the backend */
export function verifyMemberEmail(
  token: string,
  signal?: AbortSignal,
): Promise<ApiResponse<null>> {
  // encodeURIComponent makes sure special characters in the token don't break the URL
  return apiRequest<null>(
    `/members/verify?token=${encodeURIComponent(token)}`,
    { signal },
  );
}

/** The ONLY function that knows the login URL */
export function loginMember(
  values: LoginFormValues,
  signal?: AbortSignal,
): Promise<ApiResponse<LoginData>> {
  const payload: LoginMemberPayload = {
    email: values.email.trim(),
    password: values.password,
    remember: values.remember,
  };

  return apiRequest<LoginData>("/members/login", {
    method: "POST",
    body: payload,
    signal,
  });
}

/** Asks the server who the saved token belongs to */
export function getCurrentMember(
  signal?: AbortSignal,
): Promise<ApiResponse<Member>> {
  return apiRequest<Member>("/members/me", { signal });
}

/** Asks the backend for the Google sign-in address */
export function getGoogleAuthUrl(
  remember: boolean,
  signal?: AbortSignal,
): Promise<ApiResponse<GoogleAuthUrlData>> {
  return apiRequest<GoogleAuthUrlData>(
    `/members/oauth/google/redirect?remember=${remember ? 1 : 0}`,
    { signal },
  );
}

/** Asks the server to email a reset link. The reply is the same whether or not the email exists */
export function requestPasswordReset(
  email: string,
  signal?: AbortSignal,
): Promise<ApiResponse<null>> {
  return apiRequest<null>("/members/password/forgot", {
    method: "POST",
    body: { email: email.trim() },
    signal,
  });
}

/** Sends the token from the email link plus the new password */
export function resetPassword(
  token: string,
  values: ResetPasswordValues,
  signal?: AbortSignal,
): Promise<ApiResponse<null>> {
  return apiRequest<null>("/members/password/reset", {
    method: "POST",
    body: {
      token,
      password: values.password,
      password_confirmation: values.confirmPassword,
    },
    signal,
  });
}
