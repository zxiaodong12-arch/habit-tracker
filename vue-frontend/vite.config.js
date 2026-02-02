import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// 支持通过环境变量配置 base 路径
// 如果仓库名不是 username.github.io，需要设置 base 为 /仓库名/
// 例如：VITE_BASE_PATH=/habit-tracker/ npm run build
const basePath = process.env.VITE_BASE_PATH || './'

export default defineConfig({
  plugins: [vue()],
  base: basePath,  // 使用相对路径或子路径，适配静态网站托管
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
    outDir: '../dist',
    emptyOutDir: true
  }
})
