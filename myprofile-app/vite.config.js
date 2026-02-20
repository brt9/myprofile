import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        // remova a linha abaixo se o arquivo não existir
        'resources/js/axios.js',
      ],
      refresh: true,
    }),
  ],
  server: {
    host: true,                 // 0.0.0.0
    port: 5137,
    strictPort: true,
    cors: true,
    origin: 'http://127.0.0.1:5137',   // evita problemas de "localhost" no Windows
    watch: {
      usePolling: true,
      interval: 100,
      ignored: ['**/node_modules/**', '**/vendor/**', '**/storage/**'],
    },
    hmr: {
      protocol: 'ws',
      host: '127.0.0.1',
      port: 5137,
      clientPort: 5137,
    },
  },
  build: { sourcemap: false },
})
