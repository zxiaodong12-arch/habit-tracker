<template>
  <div 
    class="habit-card" 
    :class="{ completed: isCompleted }"
    :style="{ '--habit-color': habit.color }"
    @click="handleCardClick"
  >
    <div class="habit-header">
      <div class="habit-info">
        <div class="habit-emoji" :style="{ background: `${habit.color}20`, color: habit.color }">
          {{ habit.emoji || '📝' }}
        </div>
        <div class="habit-details">
          <div class="habit-name">{{ habit.name }}</div>
          <div class="habit-streak">
            <span>当前连续 <strong>{{ streak }}</strong> 天</span>
            <span class="streak-divider">·</span>
            <span>🔥 历史最长 <strong>{{ longestStreak }}</strong> 天</span>
          </div>
          <div v-if="targetProgress" class="habit-target-progress">
            <span class="target-label">{{ getTargetTypeLabel() }}进度:</span>
            <span class="target-value">{{ targetProgress.completed }}/{{ targetProgress.target_count }}</span>
            <div class="target-progress-bar">
              <div 
                class="target-progress-fill" 
                :style="{ 
                  width: `${targetProgress.progress}%`,
                  backgroundColor: habit.color 
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>
      <div class="checkbox-wrapper">
        <div 
          class="habit-checkbox" 
          :class="{ checked: isCompleted }"
          @click.stop="handleToggle"
        ></div>
      </div>
    </div>
    <div class="heatmap-preview">
      <div class="heatmap-preview-title">最近30天</div>
      <div class="heatmap-grid">
        <div 
          v-for="(record, index) in recentRecords" 
          :key="index"
          class="heatmap-day" 
          :class="{ completed: record.completed }"
          :title="`${record.date}: ${record.completed ? '已完成' : '未完成'}`"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { isCompletedToday, calculateStreak, getRecentRecords, calculateLongestStreakFromRecords } from '@/utils/habitUtils'

const props = defineProps({
  habit: {
    type: Object,
    required: true
  },
  targetProgress: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['toggle', 'click'])

const isCompleted = computed(() => isCompletedToday(props.habit))
const streak = computed(() => calculateStreak(props.habit))
const recentRecords = computed(() => getRecentRecords(props.habit, 30))
const longestStreak = computed(() => calculateLongestStreakFromRecords(props.habit))

const getTargetTypeLabel = () => {
  if (!props.targetProgress) return ''
  const labels = {
    daily: '本周',
    weekly: '本周',
    monthly: '本月',
    yearly: '今年'
  }
  return labels[props.targetProgress.target_type] || ''
}

const handleToggle = () => {
  emit('toggle', props.habit.id)
}

const handleCardClick = () => {
  emit('click', props.habit.id)
}
</script>

<style scoped>
.habit-target-progress {
  margin-top: 0.5rem;
  font-size: 0.75rem;
}

.habit-streak {
  margin-top: 0.25rem;
  font-size: 0.8rem;
  color: #666;
  display: flex;
  gap: 0.35rem;
  flex-wrap: wrap;
}

.habit-streak strong {
  color: #111;
}

.streak-divider {
  color: #ccc;
}

.target-label {
  color: #666;
  margin-right: 0.5rem;
}

.target-value {
  font-weight: 600;
  color: #333;
}

.target-progress-bar {
  width: 100%;
  height: 4px;
  background: #e5e5e5;
  border-radius: 2px;
  margin-top: 0.25rem;
  overflow: hidden;
}

.target-progress-fill {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 2px;
}
</style>
