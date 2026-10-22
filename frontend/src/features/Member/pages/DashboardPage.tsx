export function DashboardPage() {
  return (
    <main className="flex min-h-svh items-center justify-center bg-muted/40 p-4">
      <section className="w-full max-w-md rounded-xl bg-background p-8 text-center shadow-sm ring-1 ring-foreground/10">
        <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
        <p className="mt-2 text-sm text-muted-foreground">You are logged in.</p>
      </section>
    </main>
  )
}