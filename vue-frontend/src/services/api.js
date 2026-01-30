import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

// 支持环境变量配置，开发环境使用默认值
const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://habit-tracker.com:8080/api'

const api = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// 请求拦截器 - 添加 token
api.interceptors.request.use(config => {
  const authStore = useAuthStore()
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }
  return config
})

// 响应拦截器 - 处理错误
api.interceptors.response.use(
  response => {
    // 返回完整的响应对象，包含 success, data, message 等
    return response.data
  },
  error => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.clearAuth()
      // 使用 window.location 而不是 router，因为 router 可能还未初始化
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    // 返回格式化的错误信息
    const errorMessage = error.response?.data?.message || error.message || '请求失败'
    return Promise.reject(new Error(errorMessage))
  }
)

const apiService = {
  // ========== 认证相关 ==========
  async register(username, password, email = null) {
    return await api.post('/auth/register', { username, password, email })
  },
  
  async login(username, password) {
    return await api.post('/auth/login', { username, password })
  },
  
  async logout() {
    return await api.post('/auth/logout')
  },
  
  async getCurrentUser() {
    return await api.get('/auth/me')
  },
  
  // ========== 习惯管理 ==========
  async getHabits(archived = null) {
    const params = archived !== null ? { archived } : {}
    const response = await api.get('/habits', { params })
    return response.data || []
  },
  
  async getHabit(id) {
    const response = await api.get(`/habits/${id}`)
    return response.data
  },
  
  async createHabit(habit) {
    const response = await api.post('/habits', {
      name: habit.name,
      emoji: habit.emoji || '📝',
      color: habit.color || '#10b981',
      archived: habit.archived || false
    })
    return response.data
  },
  
  async updateHabit(id, updates) {
    const response = await api.put(`/habits/${id}`, updates)
    return response.data
  },
  
  async deleteHabit(id) {
    return await api.delete(`/habits/${id}`)
  },
  
  async archiveHabit(id, archived) {
    const response = await api.patch(`/habits/${id}/archive`, { archived })
    return response.data
  },
  
  // ========== 打卡记录 ==========
  async getRecords(habitId, startDate = null, endDate = null) {
    const params = {}
    if (startDate) params.start_date = startDate
    if (endDate) params.end_date = endDate
    const response = await api.get(`/records/habit/${habitId}`, { params })
    return response.data || []
  },
  
  async toggleRecord(habitId, recordDate) {
    const response = await api.post('/records/toggle', {
      habit_id: habitId,
      record_date: recordDate
    })
    return response.data
  },
  
  // ========== 统计信息 ==========
  async getHabitStats(habitId) {
    const response = await api.get(`/stats/habit/${habitId}`)
    return response.data
  },
  
  async getUserStats(userId) {
    const response = await api.get(`/stats/user/${userId}`)
    return response.data
  },
  
  // ========== 数据转换 ==========
  convertHabitFromAPI(apiHabit, records = []) {
    const recordsObj = {}
    records.forEach(record => {
      if (record.completed === 1) {
        recordsObj[record.record_date] = true
      }
    })
    
    return {
      id: apiHabit.id.toString(),
      name: apiHabit.name,
      emoji: apiHabit.emoji || '📝',
      color: apiHabit.color || '#10b981',
      records: recordsObj,
      archived: apiHabit.archived === 1,
      createdAt: apiHabit.created_at
    }
  }
}

export default apiService
