import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiService from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  
  const isAuthenticated = computed(() => !!token.value)
  const userId = computed(() => user.value?.id || null)
  
  function setAuth(newToken, newUser) {
    token.value = newToken
    user.value = newUser
    localStorage.setItem('auth_token', newToken)
    localStorage.setItem('auth_user', JSON.stringify(newUser))
  }
  
  function clearAuth() {
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }
  
  async function login(username, password) {
    try {
      const response = await apiService.login(username, password)
      if (response.success && response.data?.token) {
        setAuth(response.data.token, response.data.user)
        return { success: true }
      }
      return { success: false, message: response.message || '登录失败' }
    } catch (error) {
      return { success: false, message: error.message || '登录失败' }
    }
  }
  
  async function register(username, password, email = null) {
    try {
      const response = await apiService.register(username, password, email)
      if (response.success) {
        return { success: true, message: '注册成功，请登录' }
      }
      return { success: false, message: response.message || '注册失败' }
    } catch (error) {
      return { success: false, message: error.message || '注册失败' }
    }
  }
  
  async function logout() {
    try {
      await apiService.logout()
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '登出失败'
      console.error('登出失败:', errorMessage, error)
    } finally {
      clearAuth()
    }
  }
  
  return {
    token,
    user,
    isAuthenticated,
    userId,
    setAuth,
    clearAuth,
    login,
    register,
    logout
  }
})
