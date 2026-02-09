<template>
  <div class="container">
    <!-- 用户信息栏 -->
    <div class="user-bar">
      <span class="user-info">
        👤 欢迎，{{ authStore.user?.username || '未登录' }}
      </span>
      <button class="logout-btn" @click="handleLogout" title="登出">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </button>
    </div>

    <!-- 头部统计 -->
    <header class="header">
      <h1>习惯追踪器</h1>
      <div class="app-intro">
        <p class="app-intro-text">一个专注打卡和看到进步的极简习惯记录工具。</p>
        <div class="app-tags">
          <span class="app-tag">📅 每日打卡</span>
          <span class="app-tag">📈 连续天数</span>
          <span class="app-tag">🔥 30 天热力图</span>
          <span class="app-tag">☁️ 云端存储 · 数据安全</span>
        </div>
      </div>
      <StatsBar :stats="stats" />
    </header>

    <!-- 习惯列表 -->
    <main>
      <section class="section-block">
        <div class="section-header">
          <h2 class="section-title">正在进行的习惯</h2>
        </div>
        <div class="habits-container" v-if="habitsStore.loading">
          <div class="loading">加载中...</div>
        </div>
        <div class="habits-container" v-else-if="habitsStore.activeHabits.length === 0">
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <h3>还没有习惯</h3>
            <p>点击右下角按钮添加你的第一个习惯</p>
          </div>
        </div>
        <div class="habits-container" v-else>
          <HabitCard
            v-for="habit in habitsStore.activeHabits"
            :key="habit.id"
            :habit="habit"
            :target-progress="getTargetProgress(habit)"
            @toggle="handleToggle"
            @click="handleHabitClick"
          />
        </div>
      </section>

      <section 
        class="section-block archived-section" 
        :class="{ collapsed: habitsStore.archivedCollapsed }"
        v-if="habitsStore.archivedHabits.length > 0"
      >
        <div class="section-header">
          <h2 class="section-title">已归档的习惯</h2>
          <div class="section-actions">
            <span class="section-subtitle">{{ habitsStore.archivedHabits.length }} 个</span>
            <button 
              class="section-toggle-btn" 
              @click="habitsStore.toggleArchivedCollapsed"
            >
              {{ habitsStore.archivedCollapsed ? '展开' : '收起' }}
            </button>
          </div>
        </div>
        <div class="habits-container archived-habits" v-if="!habitsStore.archivedCollapsed">
          <HabitCard
            v-for="habit in habitsStore.archivedHabits"
            :key="habit.id"
            :habit="habit"
            @toggle="handleToggle"
            @click="handleHabitClick"
          />
        </div>
      </section>
    </main>

    <!-- 添加习惯按钮 -->
    <button class="fab" @click="showAddModal = true" title="添加新习惯">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button>

    <!-- 添加/编辑习惯模态框 -->
    <HabitModal
      :show="showAddModal || showEditModal"
      :editing-habit="editingHabit"
      @close="closeModal"
      @submit="handleHabitSubmit"
      @archive="handleArchive"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useHabitsStore } from '@/stores/habits'
import { calculateStats, getTodayString } from '@/utils/habitUtils'
import StatsBar from '@/components/StatsBar.vue'
import HabitCard from '@/components/HabitCard.vue'
import HabitModal from '@/components/HabitModal.vue'

const router = useRouter()
const authStore = useAuthStore()
const habitsStore = useHabitsStore()

const showAddModal = ref(false)
const showEditModal = ref(false)
const editingHabit = ref(null)

const stats = computed(() => calculateStats(habitsStore.habits))

onMounted(async () => {
  try {
    await habitsStore.loadHabits()
  } catch (error) {
    const errorMessage = error?.message || error?.response?.data?.message || '加载习惯失败'
    console.error('加载习惯失败:', errorMessage, error)
  }
})

const handleToggle = async (habitId) => {
  try {
    await habitsStore.toggleHabit(habitId, getTodayString())
  } catch (error) {
    const errorMessage = error?.message || error?.response?.data?.message || '切换打卡状态失败'
    console.error('切换打卡状态失败:', errorMessage, error)
  }
}

const handleHabitClick = (habitId) => {
  router.push(`/habit/${habitId}`)
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingHabit.value = null
}

const handleHabitSubmit = async (formData) => {
  try {
    if (editingHabit.value) {
      await habitsStore.updateHabit(editingHabit.value.id, formData)
    } else {
      await habitsStore.addHabit(
        formData.name, 
        formData.emoji, 
        formData.color,
        formData.target_type || 'daily',
        formData.target_count || 1,
        formData.target_start_date
      )
    }
    closeModal()
  } catch (error) {
    const errorMessage = error?.message || error?.response?.data?.message || '保存习惯失败'
    console.error('保存习惯失败:', errorMessage, error)
  }
}

const handleArchive = async () => {
  if (!editingHabit.value) return
  try {
    await habitsStore.toggleArchiveHabit(editingHabit.value.id)
    closeModal()
  } catch (error) {
    const errorMessage = error?.message || error?.response?.data?.message || '归档操作失败'
    console.error('归档操作失败:', errorMessage, error)
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

// 计算目标进度（简化版，实际应该从API获取）
const getTargetProgress = (habit) => {
  if (!habit.target_type || !habit.target_count) {
    return null
  }
  
  // 这里简化处理，实际应该调用API获取准确的进度
  // 暂时返回null，让卡片不显示进度
  // 完整实现需要在 loadHabits 时调用详情API获取进度
  return null
}
</script>

<style scoped>
/* 样式继承自全局 style.css */
</style>
