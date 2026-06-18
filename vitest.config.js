import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

// Vitest läuft die Frontend-Tests (csv-Util, Vue-Komponenten). Der Produktions-
// build bleibt unabhängig davon Webpack-basiert (@nextcloud/webpack-vue-config).
export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['tests/frontend/**/*.spec.js'],
    },
})
