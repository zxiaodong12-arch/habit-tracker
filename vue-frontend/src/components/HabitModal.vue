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
  color: '#10b981'
})

watch(() => props.editingHabit, (habit) => {
  if (habit) {
    form.value = {
      name: habit.name,
      emoji: habit.emoji || '📝',
      color: habit.color || '#10b981'
    }
  } else {
    form.value = {
      name: '',
      emoji: '📝',
      color: '#10b981'
    }
  }
}, { immediate: true })

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
/* 样式继承自全局 style.css */
</style>
