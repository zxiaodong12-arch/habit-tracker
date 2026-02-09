<template>
  <div class="habit-trend-chart">
    <div class="chart-container">
      <svg :width="chartWidth" :height="chartHeight" class="chart-svg">
        <!-- 网格线 -->
        <g class="grid-lines">
          <line 
            v-for="i in 5" 
            :key="`grid-${i}`"
            :x1="padding" 
            :y1="padding + (chartHeight - 2 * padding) / 4 * (i - 1)"
            :x2="chartWidth - padding"
            :y2="padding + (chartHeight - 2 * padding) / 4 * (i - 1)"
            stroke="#e5e5e5"
            stroke-width="1"
          />
        </g>
        
        <!-- 平均值参考线 -->
        <line
          v-if="averageValue > 0 && points.length > 0"
          :x1="padding"
          :y1="padding + chartHeight - 2 * padding - (averageValue / maxValue) * (chartHeight - 2 * padding)"
          :x2="chartWidth - padding"
          :y2="padding + chartHeight - 2 * padding - (averageValue / maxValue) * (chartHeight - 2 * padding)"
          stroke="#999"
          stroke-width="1"
          stroke-dasharray="4,4"
          opacity="0.5"
        />
        <text
          v-if="averageValue > 0 && points.length > 0"
          :x="chartWidth - padding - 5"
          :y="padding + chartHeight - 2 * padding - (averageValue / maxValue) * (chartHeight - 2 * padding) - 5"
          text-anchor="end"
          font-size="9"
          fill="#999"
        >
          平均: {{ averageValue.toFixed(1) }}
        </text>
        
        <!-- 区域填充 -->
        <defs>
          <linearGradient :id="`gradient-${habitColor.replace('#', '')}`" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" :style="{ stopColor: habitColor, stopOpacity: 0.3 }" />
            <stop offset="100%" :style="{ stopColor: habitColor, stopOpacity: 0.05 }" />
          </linearGradient>
        </defs>
        <path
          v-if="points.length > 0"
          :d="smoothAreaPath"
          :fill="`url(#gradient-${habitColor.replace('#', '')})`"
          class="area-fill"
        />
        
        <!-- 平滑曲线 -->
        <path
          v-if="points.length > 0"
          :d="smoothCurvePath"
          :stroke="habitColor"
          stroke-width="3"
          fill="none"
          class="trend-line"
        />
        
        <!-- 数据点 -->
        <g v-for="(point, index) in points" :key="index">
          <circle
            :cx="point.x"
            :cy="point.y"
            :r="6"
            :fill="habitColor"
            :stroke="'white'"
            stroke-width="2"
            class="data-point"
          >
            <title>{{ point.label }}</title>
          </circle>
          <!-- 数据标签 -->
          <text
            :x="point.x"
            :y="point.y - 12"
            text-anchor="middle"
            font-size="11"
            font-weight="600"
            :fill="habitColor"
            class="data-label"
          >
            {{ point.value }}
          </text>
        </g>
        
        <!-- X轴标签 -->
        <g class="x-axis">
          <text
            v-for="(label, index) in xLabels"
            :key="index"
            :x="label.x"
            :y="chartHeight - padding + 20"
            text-anchor="middle"
            font-size="10"
            fill="#666"
          >
            {{ label.text }}
          </text>
        </g>
        
        <!-- Y轴标签 -->
        <g class="y-axis">
          <text
            v-for="(label, index) in yLabels"
            :key="index"
            :x="padding - 10"
            :y="label.y"
            text-anchor="end"
            font-size="10"
            fill="#666"
            dominant-baseline="middle"
          >
            {{ label.text }}
          </text>
        </g>
      </svg>
    </div>
    <div class="chart-legend">
      <div class="legend-item">
        <div class="legend-dot" :style="{ backgroundColor: habitColor }"></div>
        <span>{{ getLegendLabel() }}</span>
      </div>
      <div v-if="averageValue > 0" class="legend-item">
        <div class="legend-dash" :style="{ borderColor: '#999' }"></div>
        <span>平均值: {{ averageValue.toFixed(1) }} 次</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  habitColor: {
    type: String,
    default: '#10b981'
  },
  viewType: {
    type: String,
    default: 'daily'
  }
})

const chartWidth = 600
const chartHeight = 250
const padding = 50

const maxValue = computed(() => {
  if (props.data.length === 0) return 10
  const max = Math.max(...props.data.map(d => d.completed), 1)
  // 让最大值稍微大一点，让图表顶部留出空间
  return Math.ceil(max * 1.2)
})

const averageValue = computed(() => {
  if (props.data.length === 0) return 0
  const sum = props.data.reduce((acc, d) => acc + d.completed, 0)
  return sum / props.data.length
})

const points = computed(() => {
  if (props.data.length === 0) return []
  
  const width = chartWidth - 2 * padding
  const height = chartHeight - 2 * padding
  const stepX = props.data.length > 1 ? width / (props.data.length - 1) : 0
  
  return props.data.map((item, index) => {
    const x = padding + (props.data.length > 1 ? index * stepX : width / 2)
    const y = padding + height - (item.completed / maxValue.value) * height
    
    // 支持不同的数据格式
    const periodLabel = item.period_label || item.month || item.period || ''
    const periodKey = item.period || item.month || ''
    
    return {
      x,
      y,
      value: item.completed,
      label: `${periodLabel}: ${item.completed} 次`,
      periodKey: periodKey
    }
  })
})

// 生成平滑曲线路径（使用三次贝塞尔曲线）
const smoothCurvePath = computed(() => {
  if (points.value.length === 0) return ''
  if (points.value.length === 1) {
    return `M ${points.value[0].x} ${points.value[0].y}`
  }
  
  const pts = points.value
  let path = `M ${pts[0].x} ${pts[0].y}`
  
  // 使用 Catmull-Rom 样条曲线算法生成平滑曲线
  for (let i = 0; i < pts.length - 1; i++) {
    const p0 = i > 0 ? pts[i - 1] : pts[i]
    const p1 = pts[i]
    const p2 = pts[i + 1]
    const p3 = i < pts.length - 2 ? pts[i + 2] : pts[i + 1]
    
    // 计算控制点（使用 Catmull-Rom 样条的张力参数）
    const tension = 0.5
    const cp1x = p1.x + (p2.x - p0.x) / 6 * tension
    const cp1y = p1.y + (p2.y - p0.y) / 6 * tension
    const cp2x = p2.x - (p3.x - p1.x) / 6 * tension
    const cp2y = p2.y - (p3.y - p1.y) / 6 * tension
    
    path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`
  }
  
  return path
})

// 生成平滑区域填充路径
const smoothAreaPath = computed(() => {
  if (points.value.length === 0) return ''
  
  const bottomY = chartHeight - padding
  const firstX = points.value[0].x
  const lastX = points.value[points.value.length - 1].x
  
  // 先绘制平滑曲线
  let path = smoothCurvePath.value
  
  // 然后连接到底部形成闭合区域
  path += ` L ${lastX} ${bottomY} L ${firstX} ${bottomY} Z`
  
  return path
})

const xLabels = computed(() => {
  if (props.data.length === 0) return []
  
  const width = chartWidth - 2 * padding
  const stepX = width / Math.max(props.data.length - 1, 1)
  
  // 只显示部分标签，避免拥挤
  const step = Math.ceil(props.data.length / 6)
  
  return props.data
    .filter((_, index) => index % step === 0 || index === props.data.length - 1)
    .map((item, originalIndex) => {
      const index = originalIndex * step
      // 支持不同的数据格式
      let label = ''
      if (item.period_label) {
        label = item.period_label
      } else if (item.month) {
        const month = item.month.split('-')[1]
        label = `${month}月`
      } else if (item.period) {
        label = item.period
      }
      return {
        x: padding + index * stepX,
        text: label
      }
    })
})

const yLabels = computed(() => {
  const labels = []
  const height = chartHeight - 2 * padding
  const step = maxValue.value / 4
  
  for (let i = 0; i <= 4; i++) {
    labels.push({
      y: padding + height - (i / 4) * height,
      text: Math.round(step * i)
    })
  }
  
  return labels
})

const getLegendLabel = () => {
  const labels = {
    daily: '每月完成次数',
    weekly: '每周完成次数',
    monthly: '每月完成次数',
    yearly: '每年完成次数'
  }
  return labels[props.viewType] || '完成次数'
}
</script>

<style scoped>
.habit-trend-chart {
  width: 100%;
}

.chart-container {
  width: 100%;
  overflow-x: auto;
}

.chart-svg {
  display: block;
}

.trend-line {
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.area-fill {
  opacity: 0.6;
}

.data-point {
  cursor: pointer;
  transition: all 0.2s;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

.data-point:hover {
  r: 8;
  filter: drop-shadow(0 3px 6px rgba(0,0,0,0.2));
}

.data-label {
  pointer-events: none;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.chart-legend {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
  gap: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #666;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-dash {
  width: 20px;
  height: 0;
  border-top: 2px dashed #999;
  opacity: 0.5;
}
</style>
