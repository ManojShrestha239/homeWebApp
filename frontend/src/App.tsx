import { LoginPage } from '@/features/Member/pages/LoginPage'
import { RegisterPage } from '@/features/Member/pages/RegisterPage'
import { VerifyEmailPage } from '@/features/Member/pages/VerifyEmailPage'
import { OAuthCallbackPage } from '@/features/Member/pages/OAuthCallbackPage'
import { DashboardPage } from '@/features/Member/pages/DashboardPage'

export default function App() {
  const { pathname, searchParams } = new URL(window.location.href)

  if (pathname === '/verify-email') {
    return <VerifyEmailPage token={searchParams.get('token')} />
  }

  if (pathname === '/register') {
    return <RegisterPage />
  }

  if (pathname === '/oauth/callback') {
    return <OAuthCallbackPage />
  }

  if (pathname === '/dashboard') {
    return <DashboardPage />
  }

  return <LoginPage />
}