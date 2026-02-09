<template>
  <div class="stats-bar">
    <div class="stat-item">
      <span class="stat-label">今日完成</span>
      <span class="stat-value">{{ stats.todayCompleted }}/{{ stats.todayTotal }}</span>
    </div>
    <div class="stat-item">
      <span class="stat-label">总完成率</span>
      <span class="stat-value">{{ stats.todayRate }}%</span>
    </div>
    <div class="stat-item">
      <span class="stat-label">🔥 历史最长连续天数</span>
      <span class="stat-value">
        {{ stats.longestStreak }}天
        <span v-if="stats.longestStreakHabitName">· {{ stats.longestStreakHabitName }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
/* 样式继承自全局 style.css */

/* 调整首页“历史最长连续天数”文字大小，避免换行过多 */
.stat-item:last-child .stat-value {
  font-size: 1.4rem;
  line-height: 1.3;
  white-space: nowrap;
}

.stat-item:last-child .stat-value span {
  font-size: 0.9rem;
}
</style>
