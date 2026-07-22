import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  // Explicitly pin the cache inside frontend/ so it never drifts to the project root
  cacheDir: path.resolve(__dirname, 'node_modules/.vite'),
})
