import { LoginPage } from '@/features/Member/pages/LoginPage'
import { RegisterPage } from '@/features/Member/pages/RegisterPage'
import { VerifyEmailPage } from '@/features/Member/pages/VerifyEmailPage'

export default function App() {
  const { pathname, searchParams } = new URL(window.location.href)

  if (pathname === '/verify-email') {
    return <VerifyEmailPage token={searchParams.get('token')} />
  }

  if (pathname === '/register') {
    return <RegisterPage />
  }

  return <LoginPage />
}