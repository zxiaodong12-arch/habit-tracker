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
            连续 <strong>{{ streak }}</strong> 天
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
import { isCompletedToday, calculateStreak, getRecentRecords } from '@/utils/habitUtils'

const props = defineProps({
  habit: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['toggle', 'click'])

const isCompleted = computed(() => isCompletedToday(props.habit))
const streak = computed(() => calculateStreak(props.habit))
const recentRecords = computed(() => getRecentRecords(props.habit, 30))

const handleToggle = () => {
  emit('toggle', props.habit.id)
}

const handleCardClick = () => {
  emit('click', props.habit.id)
}
</script>

<style scoped>
/* 样式继承自全局 style.css */
</style>
