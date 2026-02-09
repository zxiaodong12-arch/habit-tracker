<template>
  <div class="modal" :class="{ active: show }" @click.self="close">
    <div class="modal-content">
      <div class="modal-header">
        <h2>{{ editingHabit ? '编辑习惯' : '添加新习惯' }}</h2>
        <button class="modal-close" @click="close">×</button>
      </div>
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="habit-name">习惯名称</label>
          <input 
            type="text" 
            id="habit-name" 
            v-model="form.name" 
            required
            autofocus
            placeholder="例如：每天跑步"
          >
        </div>
        <div class="form-group">
          <label for="habit-emoji">Emoji 图标</label>
          <input 
            type="text" 
            id="habit-emoji" 
            v-model="form.emoji" 
            maxlength="2"
            placeholder="📝"
          >
        </div>
        <div class="form-group">
          <label for="habit-color">颜色</label>
          <div class="color-picker">
            <input
              id="habit-color"
              type="color"
              v-model="form.color"
            />
          </div>
        </div>
        
        <!-- 目标设置 -->
        <div class="form-group">
          <label>目标设置</label>
          <div class="target-settings">
            <div class="target-type-group">
              <label class="radio-label">
                <input 
                  type="radio" 
                  value="daily" 
                  v-model="form.target_type"
                />
                <span>每天完成</span>
              </label>
              <label class="radio-label">
                <input 
                  type="radio" 
                  value="weekly" 
                  v-model="form.target_type"
                />
                <span>每周完成</span>
              </label>
              <label class="radio-label">
                <input 
                  type="radio" 
                  value="monthly" 
                  v-model="form.target_type"
                />
                <span>每月完成</span>
              </label>
              <label class="radio-label">
                <input 
                  type="radio" 
                  value="yearly" 
                  v-model="form.target_type"
                />
                <span>每年完成</span>
              </label>
            </div>
            <div class="target-count-group">
              <label for="target-count">目标次数：</label>
              <input
                id="target-count"
                type="number"
                v-model.number="form.target_count"
                min="1"
                max="365"
                required
              />
              <span class="target-count-hint">
                {{ getTargetTypeLabel() }}
              </span>
            </div>
            <div class="target-start-date-group">
              <label for="target-start-date">目标开始日期：</label>
              <input
                id="target-start-date"
                type="date"
                v-model="form.target_start_date"
                required
              />
              <span class="target-start-date-hint">
                用于计算周期
              </span>
            </div>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            {{ editingHabit ? '保存' : '添加' }}
          </button>
          <button
            v-if="editingHabit"
            type="button"
            class="btn btn-secondary"
            @click="handleArchive"
          >
            {{ editingHabit.archived ? '取消归档' : '归档' }}
          </button>
          <button type="button" class="btn btn-secondary" @click="close">取消</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  editingHabit: Object
})

const emit = defineEmits(['close', 'submit', 'archive'])

const colors = [
  '#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', 
  '#ef4444', '#ec4899', '#06b6d4', '#84cc16'
]

const form = ref({
  name: '',
  emoji: '📝',
  color: '#10b981',
  target_type: 'daily',
  target_count: 1,
  target_start_date: new Date().toISOString().split('T')[0]
})

watch(() => props.editingHabit, (habit) => {
  if (habit) {
    form.value = {
      name: habit.name,
      emoji: habit.emoji || '📝',
      color: habit.color || '#10b981',
      target_type: habit.target_type || 'daily',
      target_count: habit.target_count || 1,
      target_start_date: habit.target_start_date || new Date().toISOString().split('T')[0]
    }
  } else {
    form.value = {
      name: '',
      emoji: '📝',
      color: '#10b981',
      target_type: 'daily',
      target_count: 1,
      target_start_date: new Date().toISOString().split('T')[0]
    }
  }
}, { immediate: true })

const getTargetTypeLabel = () => {
  const labels = {
    daily: '次/天',
    weekly: '次/周',
    monthly: '次/月',
    yearly: '次/年'
  }
  return labels[form.value.target_type] || '次'
}

const close = () => {
  emit('close')
}

const handleSubmit = () => {
  emit('submit', { ...form.value })
  close()
}

const handleArchive = () => {
  emit('archive')
}
</script>

<style scoped>
.target-settings {
  margin-top: 0.5rem;
}

.target-type-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.25rem;
  transition: background 0.2s;
}

.radio-label:hover {
  background: #f5f5f5;
}

.radio-label input[type="radio"] {
  cursor: pointer;
}

.target-count-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.target-count-group label {
  font-weight: 500;
}

.target-count-group input[type="number"] {
  width: 80px;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
}

.target-count-hint {
  color: #666;
  font-size: 0.875rem;
}

.target-start-date-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.target-start-date-group label {
  font-weight: 500;
}

.target-start-date-group input[type="date"] {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.target-start-date-hint {
  color: #666;
  font-size: 0.875rem;
}
</style>
