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
      <span class="stat-label">最长连续</span>
      <span class="stat-value">{{ stats.longestStreak }}天</span>
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
</style>
