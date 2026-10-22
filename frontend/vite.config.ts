// import react from '@vitejs/plugin-react'
// import { defineConfig } from 'vite'

// // https://vite.dev/config/
// export default defineConfig({
//   plugins: [react()],
// })

import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'     // React fast refresh
import tailwindcss from '@tailwindcss/vite'  // Tailwind v4 Vite plugin
import { fileURLToPath, URL } from 'node:url'

// Values passed in from the root .env through docker-compose
const usePolling = process.env.VITE_USE_POLLING === 'true'
const hmrClientPort = process.env.HMR_CLIENT_PORT

export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],

  resolve: {
    alias: {
      // "@/components/ui/button" maps to "src/components/ui/button"
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },

  server: {
    host: true,                               // Listen on 0.0.0.0 inside the container
    port: 5173,
    strictPort: true,                         // Fail instead of switching ports
    allowedHosts: ['localhost', '.homewebapp.manojshrestha239.com.np'],  // Replace with your tunnel domain

    watch: {
      usePolling,                             // Needed for Windows/macOS Docker Desktop
      interval: 300,                          // Milliseconds between file checks
    },

    // Only set when browsing through the HTTPS tunnel
    hmr: hmrClientPort
      ? { clientPort: Number(hmrClientPort) }
      : undefined,
  },
})