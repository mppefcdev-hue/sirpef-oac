import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from "vite-plugin-pwa";

// https://vitejs.dev/config/
export default defineConfig({
   server: { 
     host: '0.0.0.0',
     port: 81
  },
  plugins: [
    vue(),
    VitePWA({
      injectRegister: 'auto',
      registerType: 'autoUpdate',
      manifest: {
        name: 'SIRPE App',
        short_name: 'SIRPE',
        description: 'SIRPE App description',
        theme_color: '#ffffff',
        icons: [{
          src: 'pwa-64x64.png',
          sizes: '64x64',
          type: 'image/png'
        }, {
          src: 'pwa-192x192.png',
          sizes: '192x192',
          type: 'image/png'
        }, {
          src: 'pwa-512x512.png',
          sizes: '512x512',
          type: 'image/png',
          purpose: 'any'  
        }, {
          src: 'maskable-icon-512x512.png',
          sizes: '512x512',
          type: 'image/png',
          purpose: 'maskable'
        }]
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg}']
      },
      devOptions: {
        enabled: true
        /* other options */
      } 
    })
  ],
resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  define: {
    'process.env': {
      // comment this line to containere
      //VUE_APP_API_URL: "http://10.50.95.20",
      // uncomment this line to containerize
      // VUE_APP_API_URL: "http://api.store.dev.com"
    }
  },
  test: {
    globals: true,
    environment: "jsdom",
  },
})
