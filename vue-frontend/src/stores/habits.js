import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiService from '@/services/api'

export const useHabitsStore = defineStore('habits', () => {
  const habits = ref([])
  const loading = ref(false)
  const archivedCollapsed = ref(true)
  
  const activeHabits = computed(() => 
    habits.value.filter(h => !h.archived)
  )
  
  const archivedHabits = computed(() => 
    habits.value.filter(h => h.archived)
  )
  
  async function loadHabits() {
    loading.value = true
    try {
      const allApiHabits = await apiService.getHabits()
      const habitMap = new Map()
      
      for (const apiHabit of allApiHabits) {
        if (habitMap.has(apiHabit.id)) {
          console.warn('发现重复习惯，跳过:', apiHabit.id, apiHabit.name)
          continue
        }
        const records = await apiService.getRecords(apiHabit.id)
        const habit = apiService.convertHabitFromAPI(apiHabit, records)
        habitMap.set(habit.id, habit)
      }
      
      habits.value = Array.from(habitMap.values())
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '加载习惯失败'
      console.error('加载习惯失败:', errorMessage, error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  async function toggleHabit(habitId, date) {
    try {
      await apiService.toggleRecord(habitId, date)
      await loadHabits()
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '切换打卡状态失败'
      console.error('切换打卡状态失败:', errorMessage, error)
      throw error
    }
  }
  
  async function addHabit(name, emoji, color, targetType = 'daily', targetCount = 1, targetStartDate = null) {
    try {
      await apiService.createHabit({
        name: name.trim(),
        emoji: emoji || '📝',
        color: color || '#10b981',
        archived: false,
        target_type: targetType,
        target_count: targetCount,
        target_start_date: targetStartDate || new Date().toISOString().split('T')[0]
      })
      await loadHabits()
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '添加习惯失败'
      console.error('添加习惯失败:', errorMessage, error)
      throw error
    }
  }
  
  async function updateHabit(habitId, updates) {
    try {
      await apiService.updateHabit(habitId, updates)
      await loadHabits()
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '更新习惯失败'
      console.error('更新习惯失败:', errorMessage, error)
      throw error
    }
  }
  
  async function deleteHabit(habitId) {
    try {
      await apiService.deleteHabit(habitId)
      habits.value = habits.value.filter(h => h.id !== habitId)
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '删除习惯失败'
      console.error('删除习惯失败:', errorMessage, error)
      throw error
    }
  }
  
  async function toggleArchiveHabit(habitId) {
    try {
      const habit = habits.value.find(h => h.id === habitId)
      if (habit) {
        await apiService.archiveHabit(habitId, !habit.archived)
        await loadHabits()
      }
    } catch (error) {
      const errorMessage = error?.message || error?.response?.data?.message || '归档/取消归档失败'
      console.error('归档/取消归档失败:', errorMessage, error)
      throw error
    }
  }
  
  function toggleArchivedCollapsed() {
    archivedCollapsed.value = !archivedCollapsed.value
  }
  
  return {
    habits,
    loading,
    archivedCollapsed,
    activeHabits,
    archivedHabits,
    loadHabits,
    toggleHabit,
    addHabit,
    updateHabit,
    deleteHabit,
    toggleArchiveHabit,
    toggleArchivedCollapsed
  }
})
