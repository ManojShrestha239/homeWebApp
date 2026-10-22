import { Button } from "@/components/ui/button";
import { tokenStorage } from "@/shared/api/tokenStorage";
import type { Member } from "../types/member.types";

interface DashboardPageProps {
  member: Member | null;
}

export function DashboardPage({ member }: DashboardPageProps) {
  const logout = (): void => {
    tokenStorage.clear(); // removing the token ends the session
    window.location.replace("/login"); // replace, so Back doesn't return to the dashboard
  };

  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md space-y-4 rounded-xl bg-background p-8 text-center shadow-sm ring-1 ring-foreground/10">
        <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
        <p className="text-sm text-muted-foreground">
          Welcome{member ? `, ${member.full_name}` : ""}. You are logged in.
        </p>
        <Button
          type="button"
          variant="outline"
          className="w-full"
          onClick={logout}
        >
          Log out
        </Button>
      </section>
    </main>
  );
}
