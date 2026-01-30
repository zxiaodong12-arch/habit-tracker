<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <h2>{{ isLoginMode ? '欢迎回来' : '创建账号' }}</h2>
        <p class="login-subtitle">{{ isLoginMode ? '登录以继续使用习惯追踪器' : '注册新账号开始追踪你的习惯' }}</p>
      </div>
      
      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="form-group">
          <label for="username">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            用户名
          </label>
          <input 
            type="text" 
            id="username" 
            v-model="username" 
            required
            autofocus
            placeholder="请输入用户名"
          >
        </div>
        
        <transition name="slide-fade">
          <div class="form-group" v-if="!isLoginMode">
            <label for="email">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              邮箱（可选）
            </label>
            <input 
              type="email" 
              id="email" 
              v-model="email"
              placeholder="example@email.com"
            >
          </div>
        </transition>
        
        <div class="form-group">
          <label for="password">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            密码
          </label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            required
            placeholder="请输入密码"
          >
        </div>
        
        <button 
          type="submit" 
          class="btn-submit" 
          :disabled="loading"
          :class="{ loading: loading }"
        >
          <span v-if="!loading">{{ isLoginMode ? '登录' : '注册' }}</span>
          <span v-else class="loading-spinner"></span>
        </button>
        
        <div class="form-divider">
          <span>或</span>
        </div>
        
        <button 
          type="button" 
          class="btn-switch" 
          @click="toggleMode"
          :disabled="loading"
        >
          {{ isLoginMode ? '还没有账号？立即注册' : '已有账号？立即登录' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useHabitsStore } from '@/stores/habits'

const router = useRouter()
const authStore = useAuthStore()
const habitsStore = useHabitsStore()

const isLoginMode = ref(true)
const username = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)

const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value
  username.value = ''
  email.value = ''
  password.value = ''
}

const handleSubmit = async () => {
  if (loading.value) return
  
  loading.value = true
  try {
    if (isLoginMode.value) {
      const result = await authStore.login(username.value, password.value)
      if (result.success) {
        await habitsStore.loadHabits()
        router.push('/')
      }
    } else {
      const result = await authStore.register(username.value, password.value, email.value || null)
      if (result.success) {
        isLoginMode.value = true
        username.value = ''
        email.value = ''
        password.value = ''
      }
    }
  } catch (error) {
    console.error('操作失败:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.login-container::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
  animation: pulse 8s ease-in-out infinite;
  pointer-events: none;
}

.login-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 24px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12),
              0 0 0 1px rgba(16, 185, 129, 0.08);
  animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  border: 1px solid rgba(16, 185, 129, 0.1);
  position: relative;
  overflow: hidden;
}

.login-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

.login-header {
  padding: 32px 32px 24px;
  text-align: center;
}

.login-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-color);
  animation: scaleIn 0.6s ease-out 0.2s backwards;
}

.login-header h2 {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 8px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-subtitle {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

.login-form {
  padding: 0 32px 32px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
}

.form-group label svg {
  color: var(--text-secondary);
  flex-shrink: 0;
}

.form-group input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid var(--border-color);
  border-radius: 12px;
  font-size: 15px;
  background: var(--card-bg);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-sizing: border-box;
}

.form-group input::placeholder {
  color: var(--text-secondary);
  opacity: 0.6;
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1),
              0 4px 12px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}

.form-group input:hover:not(:focus) {
  border-color: rgba(16, 185, 129, 0.3);
}

.btn-submit {
  width: 100%;
  padding: 14px 24px;
  margin-top: 8px;
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  position: relative;
  overflow: hidden;
}

.btn-submit::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.btn-submit:active::before {
  width: 300px;
  height: 300px;
}

.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
  transform: translateY(-2px);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.form-divider {
  display: flex;
  align-items: center;
  margin: 24px 0;
  text-align: center;
}

.form-divider::before,
.form-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border-color);
}

.form-divider span {
  padding: 0 16px;
  font-size: 12px;
  color: var(--text-secondary);
}

.btn-switch {
  width: 100%;
  padding: 12px 24px;
  background: transparent;
  color: var(--primary-color);
  border: 2px solid var(--primary-color);
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-switch:hover:not(:disabled) {
  background: rgba(16, 185, 129, 0.05);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.btn-switch:active:not(:disabled) {
  transform: translateY(0);
}

.btn-switch:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* 过渡动画 */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* 响应式 */
@media (max-width: 600px) {
  .login-card {
    max-width: 100%;
    border-radius: 20px;
  }

  .login-header {
    padding: 24px 24px 20px;
  }

  .login-icon {
    width: 56px;
    height: 56px;
  }

  .login-header h2 {
    font-size: 24px;
  }

  .login-form {
    padding: 0 24px 24px;
  }

  .form-group input {
    padding: 12px 14px;
    font-size: 14px;
  }

  .btn-submit {
    padding: 12px 20px;
    font-size: 15px;
  }
}
</style>
