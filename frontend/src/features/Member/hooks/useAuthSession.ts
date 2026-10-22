import { useEffect, useState } from "react";
import { tokenStorage } from "@/shared/api/tokenStorage";
import { getCurrentMember } from "../api/memberApi";
import type { Member } from "../types/member.types";

export type AuthStatus = "checking" | "authenticated" | "guest";

interface UseAuthSessionResult {
  status: AuthStatus;
  member: Member | null;
}

/**
 * Decides whether the browser has a working login.
 * No saved token means guest, immediately. A saved token is checked with /me.
 */
export function useAuthSession(): UseAuthSessionResult {
  // Start as "checking" only when a token exists, so guests never see a flash of loading
  const [status, setStatus] = useState<AuthStatus>(() =>
    tokenStorage.get() ? "checking" : "guest",
  );
  const [member, setMember] = useState<Member | null>(null);

  useEffect(() => {
    const token = tokenStorage.get();
    if (!token) return;

    // "cancelled" stops a late reply from updating a component that has already moved on
    let cancelled = false;

    getCurrentMember()
      .then((response) => {
        if (cancelled) return;
        setMember(response.data);
        setStatus("authenticated");
      })
      .catch(() => {
        if (cancelled) return;
        tokenStorage.clear(); // expired or fake token: forget it
        setStatus("guest");
      });

    return () => {
      cancelled = true;
    };
  }, []);

  return { status, member };
}
