<template>
  <div class="habit-heatmap">
    <div class="heatmap-wrapper">
      <div 
        v-for="(item, index) in displayData" 
        :key="index"
        class="heatmap-day-wrapper"
        :title="getDayTooltip(item)"
      >
        <div 
          class="heatmap-day"
          :class="getDayClass(item)"
          :style="getDayStyle(item)"
        ></div>
        <div class="heatmap-date-label">{{ getDateLabel(item.date) }}</div>
      </div>
    </div>
    <div class="heatmap-legend">
      <div class="legend-item">
        <div class="legend-box completed"></div>
        <span>已完成</span>
      </div>
      <div class="legend-item">
        <div class="legend-box incomplete"></div>
        <span>未完成</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  heatmap: {
    type: Array,
    default: () => []
  },
  habitColor: {
    type: String,
    default: '#10b981'
  },
  // 视图类型：daily / weekly / monthly / yearly
  viewType: {
    type: String,
    default: 'daily'
  }
})

// 根据 viewType 生成要展示的日历数据
const displayData = computed(() => {
  const data = props.heatmap || []

  // 每日视图：保持原来的「最近30天」效果
  if (props.viewType === 'daily') {
    // 如果 heatmap 数据不足30天，生成最近30天的完整数据
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    
    const last30Days = []
    const dataMap = {}
    
    // 将 heatmap 数据转换为映射表
    data.forEach(item => {
      const dateStr = item.date
      if (dateStr) {
        let dateKey = String(dateStr)
        if (dateKey.includes(' ')) {
          dateKey = dateKey.split(' ')[0]
        }
        if (dateKey.includes('T')) {
          dateKey = dateKey.split('T')[0]
        }
        dataMap[dateKey] = item
      }
    })
    
    // 生成最近30天的数据
    for (let i = 29; i >= 0; i--) {
      const date = new Date(today)
      date.setDate(date.getDate() - i)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const dateStr = `${year}-${month}-${day}`
      
      // 如果 heatmap 中有该日期，使用 heatmap 的数据，否则创建默认数据
      if (dataMap[dateStr]) {
        last30Days.push(dataMap[dateStr])
      } else {
        last30Days.push({
          date: dateStr,
          completed: 0,
          level: 0
        })
      }
    }
    
    // 按日期倒序（最新的在前）
    return last30Days.reverse()
  }

  // 周 / 月 / 年视图：直接使用后端生成的聚合数据，取最近若干个周期
  const sorted = [...data].sort((a, b) => {
    const da = new Date(a.date)
    const db = new Date(b.date)
    return da.getTime() - db.getTime()
  })

  // 不同视图下展示的周期数量
  let limit = 12
  if (props.viewType === 'yearly') {
    limit = 5
  } else if (props.viewType === 'weekly' || props.viewType === 'monthly') {
    limit = 12
  }

  return sorted.slice(-limit).reverse()
})

const getDayClass = (item) => {
  if (!item.completed) return 'day-empty'
  return `day-level-${item.level || 3}`
}

const getDayStyle = (item) => {
  if (!item.completed) {
    return { backgroundColor: '#ebedf0' }
  }
  
  // 根据完成情况设置颜色深浅
  const levels = {
    0: '#ebedf0',
    1: '#c6e48b',
    2: '#7bc96f',
    3: '#239a3b',
    4: '#196127'
  }
  
  const level = item.level || 3
  const baseColor = levels[level] || levels[3]
  
  // 如果设置了习惯颜色，可以混合使用
  return { backgroundColor: baseColor }
}

const getDayTooltip = (item) => {
  // 周 / 月 / 年优先使用后端提供的 label
  if (props.viewType === 'weekly' && item.week_label) {
    return `${item.week_label}: ${item.completed ? '本周已完成' : '本周未完成'}`
  }
  if (props.viewType === 'monthly' && item.month_label) {
    return `${item.month_label}: ${item.completed ? '本月有完成记录' : '本月暂无完成记录'}`
  }
  if (props.viewType === 'yearly' && item.year_label) {
    return `${item.year_label}: ${item.completed ? '本年有完成记录' : '本年暂无完成记录'}`
  }

  const date = new Date(item.date)
  const dateStr = date.toLocaleDateString('zh-CN', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
  return `${dateStr}: ${item.completed ? '已完成' : '未完成'}`
}

const getDateLabel = (dateStr) => {
  // 周 / 月 / 年视图直接使用对应的 label
  if (props.viewType === 'weekly') {
    // 只显示起始日的「M/D」或简短 Week 文本，由外层 tooltip 展示完整范围
    const date = new Date(dateStr)
    const month = date.getMonth() + 1
    const day = date.getDate()
    return `${month}/${day}`
  }
  if (props.viewType === 'monthly') {
    const date = new Date(dateStr)
    const month = date.getMonth() + 1
    const year = date.getFullYear()
    return `${year % 100}/${month}` // 如 25/2 表示 2025-02
  }
  if (props.viewType === 'yearly') {
    const date = new Date(dateStr)
    const year = date.getFullYear()
    return `${year}年`
  }

  const date = new Date(dateStr)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dateOnly = new Date(date)
  dateOnly.setHours(0, 0, 0, 0)
  
  // 如果是今天，显示"今天"
  if (dateOnly.getTime() === today.getTime()) {
    return '今天'
  }
  
  // 如果是昨天，显示"昨天"
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  if (dateOnly.getTime() === yesterday.getTime()) {
    return '昨天'
  }
  
  // 其他显示月-日
  const month = date.getMonth() + 1
  const day = date.getDate()
  return `${month}/${day}`
}
</script>

<style scoped>
.habit-heatmap {
  width: 100%;
}

.heatmap-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  justify-content: flex-start;
}

.heatmap-day-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  min-width: 50px;
}

.heatmap-day {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid transparent;
  position: relative;
}

.heatmap-day:hover {
  transform: scale(1.15);
  z-index: 1;
  border-color: rgba(0, 0, 0, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.day-empty {
  background-color: #ebedf0;
  border-color: #d1d5db;
}

.day-level-1 {
  background-color: #c6e48b;
}

.day-level-2 {
  background-color: #7bc96f;
}

.day-level-3 {
  background-color: #239a3b;
}

.day-level-4 {
  background-color: #196127;
}

.heatmap-date-label {
  font-size: 0.75rem;
  color: #666;
  text-align: center;
  white-space: nowrap;
  font-weight: 500;
}

.heatmap-legend {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  font-size: 0.875rem;
  color: #666;
  padding-top: 1rem;
  border-top: 1px solid #e5e5e5;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-box {
  width: 16px;
  height: 16px;
  border-radius: 4px;
}

.legend-box.completed {
  background-color: #239a3b;
}

.legend-box.incomplete {
  background-color: #ebedf0;
  border: 1px solid #d1d5db;
}

@media (max-width: 768px) {
  .heatmap-wrapper {
    gap: 0.5rem;
  }
  
  .heatmap-day-wrapper {
    min-width: 45px;
  }
  
  .heatmap-day {
    width: 35px;
    height: 35px;
  }
  
  .heatmap-date-label {
    font-size: 0.7rem;
  }
}
</style>
