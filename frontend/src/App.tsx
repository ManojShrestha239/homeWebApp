import { useEffect } from "react";
import { useAuthSession } from "@/features/Member/hooks/useAuthSession";
import { DashboardPage } from "@/features/Member/pages/DashboardPage";
import { LoginPage } from "@/features/Member/pages/LoginPage";
import { OAuthCallbackPage } from "@/features/Member/pages/OAuthCallbackPage";
import { RegisterPage } from "@/features/Member/pages/RegisterPage";
import { VerifyEmailPage } from "@/features/Member/pages/VerifyEmailPage";
import { ForgotPasswordPage } from "@/features/Member/pages/ForgotPasswordPage";
import { ResetPasswordPage } from "@/features/Member/pages/ResetPasswordPage";

type Access = "public" | "guest" | "protected";

// One place that defines who can see each page
const ROUTES: Record<string, Access> = {
  "/verify-email": "public", // needs the ?token= from the email link, not a login
  "/oauth/callback": "public", // Google redirect lands here before any login exists
  "/reset-password": "public", // needs ?token= from the email, not a login
  "/": "guest",
  "/register": "guest",
  "/login": "guest",
  "/forgot-password": "guest", // guests only
  "/dashboard": "protected",
};

/** Sends the browser to another page. replace() keeps the old page out of Back-button history */
function Redirect({ to }: { to: string }) {
  useEffect(() => {
    window.location.replace(to);
  }, [to]);

  return <Loading text="Redirecting…" />;
}

function Loading({ text }: { text: string }) {
  return (
    <p className="p-8 text-center text-sm text-muted-foreground">{text}</p>
  );
}

export default function App() {
  const { pathname, searchParams } = new URL(window.location.href);

  // Normalize "/login/" to "/login", and "" to "/"
  const path = pathname.replace(/\/+$/, "") || "/";

  // Unknown paths go to the public-facing start page
  const access = ROUTES[path];
  if (access === undefined) return <Redirect to="/" />;

  // Public pages render directly. The session check never runs for them.
  if (access === "public") {
    if (path === "/verify-email") {
      const token = searchParams.get("token");
      if (!token) return <Redirect to="/" />;
      return <VerifyEmailPage token={token} />;
    }

    if (path === "/reset-password") {
      const token = searchParams.get("token");
      if (!token) return <Redirect to="/forgot-password" />;
      return <ResetPasswordPage token={token} />;
    }
    return <OAuthCallbackPage />;
  }

  // Guest and protected pages go through the session gate
  return <AuthGate access={access} path={path} />;
}

interface AuthGateProps {
  access: "guest" | "protected";
  path: string;
}

function AuthGate({ access, path }: AuthGateProps) {
  const auth = useAuthSession();

  // Wait until we know whether a valid login exists
  if (auth.status === "checking") {
    return <Loading text="Checking your session…" />;
  }

  // Protected: only logged-in members may see it
  if (access === "protected") {
    if (auth.status === "guest") return <Redirect to="/" />;

    if (path === "/dashboard") return <DashboardPage member={auth.member} />;
    return <DashboardPage member={auth.member} />;
  }

  // Guest-only: logged-in members are sent to the dashboard
  if (auth.status === "authenticated") return <Redirect to="/dashboard" />;
  if (path === "/forgot-password") return <ForgotPasswordPage />;
  if (path === "/register") return <RegisterPage />;
  return <LoginPage />; // "/", "/login", and any other guest route show the login page
}
