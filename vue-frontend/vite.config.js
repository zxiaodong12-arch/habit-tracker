// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
    plugins: [vue()],
    base: '/habit-tracker-dist/',  // 必须设置为你的仓库名
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    },
    server: {
        port: 3000,
        open: true
    },
    build: {
        outDir: 'dist',  // 确保在项目内
        emptyOutDir: true,
        // 可选：添加manifest文件帮助调试
        manifest: true,
        // 确保资源文件正确命名
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name]-[hash][extname]',
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js'
            }
        }
    }
})