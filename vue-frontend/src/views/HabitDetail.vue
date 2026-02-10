<template>
  <div class="container">
    <!-- 头部 -->
    <header class="detail-header" v-if="habit && habit.id">
      <button class="back-btn" @click="goBack">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </button>
      <div class="habit-title">
        <span class="habit-emoji" :style="{ background: `${habit.color || '#10b981'}20`, color: habit.color || '#10b981' }">
          {{ habit.emoji || '📝' }}
        </span>
        <h1>{{ habit.name || '加载中...' }}</h1>
      </div>
      <button class="edit-btn" @click="showEditModal = true" title="编辑习惯">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
      </button>
    </header>

    <!-- 加载状态 -->
    <div v-if="loading" class="loading-container">
      <div class="loading">加载中...</div>
    </div>

    <!-- 内容 -->
    <div v-else-if="habit && habit.id" class="detail-content">
      <!-- 统计卡片 -->
      <section class="section-block">
        <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-value">{{ stats.total_periods || 0 }}</div>
          <div class="stat-label">{{ stats.total_periods_label || '总天数' }}</div>
          <div class="stat-desc">{{ getTotalPeriodsDesc() }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ stats.completion_rate || 0 }}%</div>
          <div class="stat-label">完成率</div>
          <div class="stat-desc">{{ stats.completion_rate_desc || '已完成数 / 总数' }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ stats.current_streak || 0 }}</div>
          <div class="stat-label">{{ stats.current_streak_label || '连续天数' }}</div>
          <div class="stat-desc">{{ stats.current_streak_desc || '当前连续完成天数' }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ stats.longest_streak || 0 }}</div>
          <div class="stat-label">{{ stats.longest_streak_label || '最长连续' }}</div>
          <div class="stat-desc">{{ stats.longest_streak_desc || '历史最长连续天数' }}</div>
        </div>
        </div>
      </section>

      <!-- 目标进度 -->
      <section v-if="targetProgress" class="section-block">
        <div class="target-progress-card">
        <div class="target-progress-header">
          <h3>{{ getTargetTypeLabel() }}目标进度</h3>
          <span 
            class="target-progress-text"
            v-if="!isTargetCompleted"
          >
            {{ targetProgress.completed }} / {{ targetProgress.target_count }} 次
          </span>
          <span 
            class="target-progress-text"
            v-else
          >
            {{ getTargetPeriodPrefix() }}已完成 {{ targetProgress.completed }} 次
          </span>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ 
              width: `${targetProgress.progress}%`,
              backgroundColor: habit.color || '#10b981'
            }"
          ></div>
        </div>
        <div 
          v-if="isTargetCompleted"
          class="target-remaining"
        >
          本周期目标已完成
          <span v-if="targetProgress.completed > targetProgress.target_count">
            ，已超出 {{ targetProgress.completed - targetProgress.target_count }} 次
          </span>
        </div>
        <div 
          v-else-if="targetProgress.remaining_days > 0" 
          class="target-remaining"
        >
          还剩 {{ targetProgress.remaining_days }} 天完成目标
        </div>
        <div 
          v-else
          class="target-remaining"
        >
          {{ getTargetPeriodPrefix() }}目标未完成
        </div>
        </div>
      </section>

      <!-- 视图热力图 -->
      <section class="section-block" v-if="viewData && viewData.heatmap">
        <div class="section-header">
          <h2 class="section-title">{{ getViewHeatmapTitle() }}</h2>
        </div>
        <HabitHeatmap 
          :heatmap="viewData.heatmap" 
          :habit-color="habit.color || '#10b981'"
          :view-type="viewData.view_type || 'daily'"
        />
      </section>

      <!-- 趋势图 -->
      <section class="section-block" v-if="viewData && viewData.trend">
        <div class="section-header">
          <h2 class="section-title">{{ viewData.trend_label || '趋势' }}</h2>
        </div>
        <HabitTrendChart 
          :data="viewData.trend" 
          :habit-color="habit.color || '#10b981'"
          :view-type="viewData.view_type || 'daily'"
        />
      </section>

      <!-- 打卡记录 - 日视图 -->
      <section class="section-block" v-if="viewData && viewData.view_type === 'daily'">
        <div class="section-header">
          <h2 class="section-title">打卡记录</h2>
          <div class="filter-controls">
            <select v-model="filterMonth" class="filter-select" @change="applyFilters">
              <option value="">全部月份</option>
              <option v-for="month in availableMonths" :key="month" :value="month">
                {{ month }}
              </option>
            </select>
          </div>
        </div>
        
        <div class="records-list-container">
          <!-- 按月份分组显示所有日期 -->
          <div v-for="(group, month) in groupedAllDates" :key="month" class="record-month-group">
            <div class="month-header">
              <span class="month-label">{{ formatMonth(month) }}</span>
              <span class="month-count">
                {{ getCompletedCount(group) }} / {{ group.length }} 天
              </span>
            </div>
            <div class="records-grid">
              <div 
                v-for="dateItem in group" 
                :key="dateItem.date"
                class="record-card"
                :class="{ 
                  completed: dateItem.completed && !dateItem.isFuture, 
                  incomplete: !dateItem.completed && !dateItem.isFuture,
                  future: dateItem.isFuture
                }"
              >
                <div class="record-day">{{ getDay(dateItem.date) }}</div>
                <div class="record-status-icon">
                  <svg v-if="dateItem.completed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <svg v-else-if="dateItem.isFuture" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3">
                    <circle cx="12" cy="12" r="10"></circle>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </div>
                <div class="record-date-text">{{ formatDateShort(dateItem.date) }}</div>
              </div>
            </div>
          </div>
          
          <div v-if="allDates.length === 0" class="empty-records">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p>暂无记录</p>
          </div>
        </div>
        
        <!-- 分页控制 -->
        <div v-if="totalPages > 1" class="pagination">
          <button 
            class="page-btn" 
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            上一页
          </button>
          <span class="page-info">第 {{ currentPage }} / {{ totalPages }} 页</span>
          <button 
            class="page-btn" 
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            下一页
          </button>
        </div>
      </section>

      <!-- 打卡记录 - 周/月/年视图 -->
      <section class="section-block" v-if="viewData && viewData.view_type !== 'daily' && viewData.trend">
        <div class="section-header">
          <h2 class="section-title">{{ getRecordsViewTitle() }}</h2>
        </div>
        <div class="records-list-container">
          <div class="period-records-grid">
            <div 
              v-for="(item, index) in viewData.trend" 
              :key="index"
              class="period-record-card"
              :class="{ 
                completed: item.completed > 0, 
                incomplete: item.completed === 0
              }"
            >
              <div class="period-label">{{ item.period_label || item.period || item.month }}</div>
              <div class="period-status-icon">
                <svg v-if="item.completed > 0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </div>
              <div class="period-count">{{ item.completed }} / {{ item.total }}</div>
            </div>
          </div>
          
          <div v-if="!viewData.trend || viewData.trend.length === 0" class="empty-records">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p>暂无记录</p>
          </div>
        </div>
      </section>
    </div>

    <!-- 编辑习惯模态框 -->
    <HabitModal
      :show="showEditModal"
      :editing-habit="editingHabit"
      @close="closeEditModal"
      @submit="handleEditSubmit"
      @archive="handleArchive"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiService from '@/services/api'
import HabitHeatmap from '@/components/HabitHeatmap.vue'
import HabitTrendChart from '@/components/HabitTrendChart.vue'
import HabitModal from '@/components/HabitModal.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const habit = ref({
  id: null,
  name: '',
  emoji: '📝',
  color: '#10b981'
})
const stats = ref({
  total_days: 0,
  completed_days: 0,
  completion_rate: 0,
  current_streak: 0,
  longest_streak: 0,
  total_records: 0
})
const targetProgress = ref(null)
const viewData = ref(null)
const recentRecords = ref([])

// 筛选和分页
const filterMonth = ref('')
const currentPage = ref(1)
const pageSize = 30 // 每页显示30条记录

// 获取所有可用月份（只从 recent_records 中提取，不包括 heatmap）
const availableMonths = computed(() => {
  const months = new Set()
  
  // 只从 recent_records 中提取月份（有实际记录的日期）
  recentRecords.value.forEach(record => {
    const date = record.record_date || record.date
    if (date) {
      let dateStr = String(date)
      if (dateStr.includes(' ')) {
        dateStr = dateStr.split(' ')[0]
      }
      if (dateStr.includes('T')) {
        dateStr = dateStr.split('T')[0]
      }
      
      // 提取年月部分 (YYYY-MM)
      const month = dateStr.substring(0, 7)
      if (month && month.match(/^\d{4}-\d{2}$/)) {
        months.add(month)
      }
    }
  })
  
  // 转换为数组，按时间倒序排序（最新的在前）
  return Array.from(months).sort().reverse()
})

// 创建记录映射表（快速查找）
// 存储所有有记录的日期，包括已完成和未完成的
// 优先使用 recent_records，如果没有则使用 heatmap 数据补充
const recordsMap = computed(() => {
  const map = {}
  
  // 先从 recent_records 构建映射
  recentRecords.value.forEach(record => {
    // 后端返回的字段是 record_date (DATE 类型，格式为 YYYY-MM-DD)
    const date = record.record_date || record.date
    if (date) {
      // 确保日期格式一致（YYYY-MM-DD）
      let dateStr = String(date)
      // 处理可能的日期时间格式
      if (dateStr.includes(' ')) {
        dateStr = dateStr.split(' ')[0] // 只取日期部分，去掉时间
      }
      if (dateStr.includes('T')) {
        dateStr = dateStr.split('T')[0] // 处理 ISO 格式
      }
      
      // 数据库返回的 completed 是 TINYINT(1)，值为 0 或 1
      // 只有 completed === 1 才算完成
      const completedValue = record.completed
      const isCompleted = completedValue === 1 || 
                         completedValue === true || 
                         String(completedValue) === '1'
      
      map[dateStr] = isCompleted
    }
  })
  
  // 使用 viewData.heatmap 数据补充未完成的记录（仅 daily 类型）
  // heatmap 包含所有日期（包括未完成的），可以补充 recent_records 中缺失的记录
  if (viewData.value && viewData.value.heatmap && viewData.value.view_type === 'daily') {
    viewData.value.heatmap.forEach(item => {
      const date = item.date
      if (date) {
        let dateStr = String(date)
        if (dateStr.includes(' ')) {
          dateStr = dateStr.split(' ')[0]
        }
        if (dateStr.includes('T')) {
          dateStr = dateStr.split('T')[0]
        }
        
        // 如果 recent_records 中没有该日期，使用 heatmap 中的“已完成”数据补充
        if (!(dateStr in map)) {
          const isCompleted = item.completed === 1 || 
                             item.completed === true || 
                             String(item.completed) === '1'
          // 只在已完成时补充，未完成的日期保持 undefined，
          // 这样在日历中会按“无记录”处理，由 earliestRecordDate 和 today 决定是未来还是未完成
          if (isCompleted) {
            map[dateStr] = true
          }
        }
      }
    })
  }
  
  return map
})

// 计算最早记录日期
const earliestRecordDate = computed(() => {
  if (recentRecords.value.length === 0) {
    return null
  }
  
  let earliest = null
  recentRecords.value.forEach(record => {
    const date = record.record_date || record.date
    if (date) {
      let dateStr = String(date)
      if (dateStr.includes(' ')) {
        dateStr = dateStr.split(' ')[0]
      }
      if (dateStr.includes('T')) {
        dateStr = dateStr.split('T')[0]
      }
      
      const recordDate = new Date(dateStr)
      if (!earliest || recordDate < earliest) {
        earliest = recordDate
      }
    }
  })
  
  return earliest
})

// 生成所有日期（按月份显示完整天数）
const allDates = computed(() => {
  const today = new Date()
  const todayYear = today.getFullYear()
  const todayMonth = today.getMonth()
  const todayDay = today.getDate()
  const todayDate = new Date(todayYear, todayMonth, todayDay)
  
  const dates = []
  
  if (filterMonth.value) {
    // 如果筛选了月份，显示该月的所有日期（从1号到最后一天）
    const [year, month] = filterMonth.value.split('-').map(Number)
    const lastDay = new Date(year, month, 0).getDate()
    
    for (let day = 1; day <= lastDay; day++) {
      const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
      const currentDateOnly = new Date(year, month - 1, day)
      
      // 检查记录映射表
      const recordStatus = recordsMap.value[dateStr]
      
      // 如果 recordsMap 中有该日期，说明有记录，不算未来日期
      // 只有没有记录，且日期晚于今天，才算未来日期
      const hasRecord = recordStatus !== undefined
      
      // 如果日期早于最早记录日期，也算作"未来日期"（习惯开始前）
      const isBeforeEarliest = earliestRecordDate.value && currentDateOnly < earliestRecordDate.value
      const isFuture = !hasRecord && (currentDateOnly > todayDate || isBeforeEarliest)
      
      // 只有明确为 true 才算完成，undefined 或 false 都算未完成
      const completed = recordStatus === true
      
      dates.push({
        date: dateStr,
        completed: completed,
        isFuture: isFuture
      })
    }
  } else {
    // 如果没有筛选，从所有记录中提取月份，然后生成这些月份的完整日期
    // 只有当 recent_records 中有记录时才显示打卡记录
    if (recentRecords.value.length === 0) {
      return []
    }
    
    const monthsSet = new Set()
    
    // 从 recent_records 中提取月份（只提取有实际记录的日期）
    recentRecords.value.forEach(record => {
      const date = record.record_date || record.date
      if (date) {
        let dateStr = String(date)
        if (dateStr.includes(' ')) {
          dateStr = dateStr.split(' ')[0]
        }
        if (dateStr.includes('T')) {
          dateStr = dateStr.split('T')[0]
        }
        const month = dateStr.substring(0, 7) // YYYY-MM
        if (month && month.match(/^\d{4}-\d{2}$/)) {
          monthsSet.add(month)
        }
      }
    })
    
    // 如果没有任何月份，返回空数组
    if (monthsSet.size === 0) {
      return []
    }
    
    // 获取所有月份，按时间正序排序
    const sortedMonths = Array.from(monthsSet).sort()
    
    // 遍历所有有记录的月份，生成每个月份的完整日期
    sortedMonths.forEach(monthStr => {
      const [year, month] = monthStr.split('-').map(Number)
      const lastDay = new Date(year, month, 0).getDate() // 获取该月的最后一天
      
      // 生成该月的所有日期
      for (let day = 1; day <= lastDay; day++) {
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
        const currentDateOnly = new Date(year, month - 1, day)
        
        // 检查记录映射表
        const recordStatus = recordsMap.value[dateStr]
        
        // 如果 recordsMap 中有该日期，说明有记录，不算未来日期
        // 只有没有记录，且日期晚于今天，才算未来日期
        const hasRecord = recordStatus !== undefined
        
        // 如果日期早于最早记录日期，也算作"未来日期"（习惯开始前）
        const isBeforeEarliest = earliestRecordDate.value && currentDateOnly < earliestRecordDate.value
        const isFuture = !hasRecord && (currentDateOnly > todayDate || isBeforeEarliest)
        
        // 只有明确为 true 才算完成，undefined 或 false 都算未完成
        const completed = recordStatus === true
        
        dates.push({
          date: dateStr,
          completed: completed,
          isFuture: isFuture
        })
      }
    })
  }
  
  // 按日期正序排序（从早到晚）
  dates.sort((a, b) => a.date.localeCompare(b.date))
  
  return dates
})

// 按月份分组所有日期（不分页，直接按月份分组）
const groupedAllDates = computed(() => {
  // 先按月份分组所有日期
  const allGroups = {}
  allDates.value.forEach(dateItem => {
    const month = dateItem.date.substring(0, 7) // YYYY-MM
    if (!allGroups[month]) {
      allGroups[month] = []
    }
    allGroups[month].push(dateItem)
  })
  
  // 对每个月份内的日期按日期正序排序
  Object.keys(allGroups).forEach(month => {
    allGroups[month].sort((a, b) => a.date.localeCompare(b.date))
  })
  
  // 获取所有月份，按时间倒序排序（最近的月份在前）
  const sortedMonths = Object.keys(allGroups).sort().reverse()
  
  // 按月份分页（每页显示2个月份）
  const monthsPerPage = 2
  const startMonthIndex = (currentPage.value - 1) * monthsPerPage
  const endMonthIndex = startMonthIndex + monthsPerPage
  const paginatedMonths = sortedMonths.slice(startMonthIndex, endMonthIndex)
  
  // 只返回当前页的月份数据
  const paginatedGroups = {}
  paginatedMonths.forEach(month => {
    paginatedGroups[month] = allGroups[month]
  })
  
  return paginatedGroups
})

// 获取已完成数量
const getCompletedCount = (dates) => {
  return dates.filter(d => d.completed).length
}

// 总页数（按月份分页）
const totalPages = computed(() => {
  // 计算所有月份的数量
  const allMonths = new Set()
  allDates.value.forEach(dateItem => {
    const month = dateItem.date.substring(0, 7)
    allMonths.add(month)
  })
  const monthCount = allMonths.size
  
  // 每页显示2个月份
  const monthsPerPage = 2
  return Math.max(1, Math.ceil(monthCount / monthsPerPage))
})

const applyFilters = () => {
  currentPage.value = 1 // 重置到第一页
}

onMounted(async () => {
  await loadDetail()
})

const loadDetail = async () => {
  try {
    loading.value = true
    const data = await apiService.getHabitDetail(route.params.id)
    
    // getHabitDetail 已经返回了 data 部分，直接使用
    if (data && data.habit) {
      habit.value = {
        id: data.habit.id,
        name: data.habit.name || '',
        emoji: data.habit.emoji || '📝',
        color: data.habit.color || '#10b981',
        ...data.habit
      }
      stats.value = data.stats || stats.value
      targetProgress.value = data.target_progress || null
      viewData.value = data.view_data || null
      
      // 兼容旧数据格式
      if (!viewData.value && (data.heatmap || data.monthly_trend)) {
        viewData.value = {
          view_type: 'daily',
          heatmap: data.heatmap || [],
          trend: data.monthly_trend || [],
          trend_label: '月度趋势'
        }
      }
      
      // 获取所有记录，不限制数量（前端分页）
      recentRecords.value = data.recent_records || []
    } else {
      throw new Error('返回数据格式错误: ' + JSON.stringify(data))
    }
  } catch (error) {
    console.error('加载习惯详情失败:', error)
    console.error('完整响应:', error.response || error)
    alert('加载失败: ' + (error.message || '未知错误'))
    // 加载失败时返回首页
    router.push('/')
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/')
}

// 编辑功能
const showEditModal = ref(false)
const editingHabit = computed(() => {
  if (!habit.value || !habit.value.id) return null
  return {
    id: habit.value.id,
    name: habit.value.name,
    emoji: habit.value.emoji,
    color: habit.value.color,
    target_type: habit.value.target_type || 'daily',
    target_count: habit.value.target_count || 1,
    target_start_date: habit.value.target_start_date || new Date().toISOString().split('T')[0],
    archived: habit.value.archived || false
  }
})

const closeEditModal = () => {
  showEditModal.value = false
}

const handleEditSubmit = async (formData) => {
  try {
    await apiService.updateHabit(habit.value.id, formData)
    // 重新加载详情
    await loadDetail()
    closeEditModal()
  } catch (error) {
    console.error('更新习惯失败:', error)
    alert('更新习惯失败: ' + (error?.message || '未知错误'))
  }
}

const handleArchive = async () => {
  try {
    const newArchivedStatus = !habit.value.archived
    await apiService.archiveHabit(habit.value.id, newArchivedStatus)
    // 重新加载详情
    await loadDetail()
    closeEditModal()
    // 如果已归档，返回首页
    if (newArchivedStatus) {
      router.push('/')
    }
  } catch (error) {
    console.error('归档操作失败:', error)
    alert('归档操作失败: ' + (error?.message || '未知错误'))
  }
}

const getTargetTypeLabel = () => {
  if (!targetProgress.value) return ''
  const labels = {
    daily: '每天',
    weekly: '每周',
    monthly: '每月',
    yearly: '每年'
  }
  return labels[targetProgress.value.target_type] || ''
}

const getTargetPeriodPrefix = () => {
  if (!targetProgress.value) return '本周期'
  const labels = {
    daily: '本日',
    weekly: '本周',
    monthly: '本月',
    yearly: '本年'
  }
  return labels[targetProgress.value.target_type] || '本周期'
}

const isTargetCompleted = computed(() => {
  if (!targetProgress.value) return false
  // 当完成次数 >= 目标次数时视为本周期目标已完成
  return Number(targetProgress.value.completed || 0) >= Number(targetProgress.value.target_count || 0)
})

const getTotalPeriodsDesc = () => {
  if (!stats.value || !stats.value.target_type) {
    return '从最早记录到今天'
  }
  const descs = {
    daily: '从最早记录到今天',
    weekly: '从最早记录周到今天',
    monthly: '从最早记录月到今天',
    yearly: '从最早记录年到今天'
  }
  return descs[stats.value.target_type] || '从最早记录到今天'
}

const getViewHeatmapTitle = () => {
  if (!viewData.value || !viewData.value.view_type) {
    return '最近三十天打卡日历'
  }
  const titles = {
    daily: '最近三十天打卡日历',
    weekly: '最近周打卡日历',
    monthly: '最近月打卡日历',
    yearly: '年度打卡日历'
  }
  return titles[viewData.value.view_type] || '最近三十天打卡日历'
}

const getRecordsViewTitle = () => {
  if (!viewData.value || !viewData.value.view_type) {
    return '打卡记录'
  }
  const titles = {
    daily: '打卡记录',
    weekly: '周打卡记录',
    monthly: '月打卡记录',
    yearly: '年打卡记录'
  }
  return titles[viewData.value.view_type] || '打卡记录'
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const formatDateShort = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN', { 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatMonth = (monthStr) => {
  if (!monthStr) return ''
  // 确保格式是 YYYY-MM
  if (!monthStr.match(/^\d{4}-\d{2}$/)) {
    return monthStr
  }
  const [year, month] = monthStr.split('-')
  const monthNum = parseInt(month, 10)
  if (isNaN(monthNum) || monthNum < 1 || monthNum > 12) {
    return monthStr
  }
  const monthNames = ['一月', '二月', '三月', '四月', '五月', '六月', 
                      '七月', '八月', '九月', '十月', '十一月', '十二月']
  return `${year}年${monthNames[monthNum - 1]}`
}

const getDay = (dateStr) => {
  const date = new Date(dateStr)
  return date.getDate()
}
</script>

<style scoped>
.habit-detail {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 2rem;
}

.detail-header {
  background: white;
  padding: 1rem;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 10;
}

.back-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  color: #666;
  transition: color 0.2s;
}

.back-btn:hover {
  color: #333;
}

.habit-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.edit-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  color: #666;
  transition: color 0.2s;
  margin-left: auto;
}

.edit-btn:hover {
  color: #333;
}

.habit-emoji {
  font-size: 2rem;
  width: 3rem;
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
}

.habit-title h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.loading-container {
  padding: 3rem;
  text-align: center;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.stat-desc {
  font-size: 0.75rem;
  color: #999;
  margin-top: 0.25rem;
}

.target-progress-card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.target-progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.target-progress-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.target-progress-text {
  font-size: 1.125rem;
  font-weight: 600;
  color: #666;
}

.progress-bar {
  width: 100%;
  height: 1rem;
  background: #e5e5e5;
  border-radius: 0.5rem;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 0.5rem;
}

.target-remaining {
  font-size: 0.875rem;
  color: #666;
  text-align: center;
}

/* 使用全局的 .section-block 和 .section-title 样式 */

.section-header-with-filter {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-controls {
  display: flex;
  gap: 0.5rem;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  background: white;
  cursor: pointer;
  color: #333;
}

.filter-select:hover {
  border-color: #999;
}

.records-list-container {
  max-height: 600px;
  overflow-y: auto;
}

.record-month-group {
  margin-bottom: 2rem;
}

.month-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 2px solid #e5e5e5;
  margin-bottom: 1rem;
}

.month-label {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.month-count {
  font-size: 0.875rem;
  color: #666;
}

.records-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 0.75rem;
}

.record-card {
  background: white;
  border: 2px solid #e5e5e5;
  border-radius: 0.5rem;
  padding: 0.75rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.record-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.record-card.completed {
  border-color: #10b981;
  background: linear-gradient(135deg, #10b98110 0%, #10b98105 100%);
}

.record-card.incomplete {
  border-color: #fbbf24;
  background: linear-gradient(135deg, #fef3c710 0%, #fef3c705 100%);
}

.record-card.future {
  border-color: #e5e5e5;
  background: #fafafa;
  opacity: 0.5;
}

.record-day {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
}

.record-card.completed .record-day {
  color: #10b981;
}

.record-card.incomplete .record-day {
  color: #f59e0b;
}

.record-card.future .record-day {
  color: #999;
}

.record-status-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.record-card.completed .record-status-icon {
  color: #10b981;
}

.record-card.incomplete .record-status-icon {
  color: #f59e0b;
}

.record-card.future .record-status-icon {
  color: #ccc;
}

.record-date-text {
  font-size: 0.75rem;
  color: #666;
}

.record-card.completed .record-date-text {
  color: #10b981;
  font-weight: 500;
}

.record-card.incomplete .record-date-text {
  color: #f59e0b;
  font-weight: 500;
}

.record-card.future .record-date-text {
  color: #999;
}

.period-records-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  padding: 1rem 0;
}

.period-record-card {
  background: white;
  border: 2px solid #e5e5e5;
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
  transition: all 0.2s;
  cursor: pointer;
}

.period-record-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.period-record-card.completed {
  border-color: #10b981;
  background: linear-gradient(135deg, #10b98110 0%, #10b98105 100%);
}

.period-record-card.incomplete {
  border-color: #fbbf24;
  background: linear-gradient(135deg, #fef3c710 0%, #fef3c705 100%);
}

.period-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.period-record-card.completed .period-label {
  color: #10b981;
}

.period-record-card.incomplete .period-label {
  color: #f59e0b;
}

.period-status-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0.5rem 0;
}

.period-record-card.completed .period-status-icon {
  color: #10b981;
}

.period-record-card.incomplete .period-status-icon {
  color: #f59e0b;
}

.period-count {
  font-size: 0.75rem;
  color: #666;
  margin-top: 0.5rem;
}

.period-record-card.completed .period-count {
  color: #10b981;
  font-weight: 500;
}

.period-record-card.incomplete .period-count {
  color: #f59e0b;
  font-weight: 500;
}

.empty-records {
  text-align: center;
  padding: 3rem;
  color: #999;
}

.empty-records svg {
  margin: 0 auto 1rem;
  color: #ccc;
}

.empty-records p {
  margin: 0;
  font-size: 0.875rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e5e5;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 0.375rem;
  background: white;
  cursor: pointer;
  font-size: 0.875rem;
  color: #333;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  border-color: #999;
  background: #f5f5f5;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.875rem;
  color: #666;
}
</style>
