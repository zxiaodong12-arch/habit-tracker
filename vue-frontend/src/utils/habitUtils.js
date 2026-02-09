// 习惯相关的工具函数

export function getTodayString() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function getTodayStringFromDate(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function isCompletedToday(habit) {
  const today = getTodayString()
  return habit.records && habit.records[today] === true
}

export function calculateStreak(habit) {
  const records = habit.records || {}
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  let streak = 0
  let currentDate = new Date(today)
  
  while (true) {
    const dateStr = getTodayStringFromDate(currentDate)
    if (records[dateStr] === true) {
      streak++
      currentDate.setDate(currentDate.getDate() - 1)
    } else {
      break
    }
  }
  
  return streak
}

export function getCompletionRateInfo(habit) {
  const records = habit.records || {}
  const completedDates = Object.keys(records)
    .filter(date => records[date] === true)
    .sort()
  
  if (completedDates.length === 0) {
    return {
      rate: 0,
      completedDays: 0,
      totalDays: 0
    }
  }
  
  const firstDateParts = completedDates[0].split('-')
  const firstDate = new Date(
    parseInt(firstDateParts[0]),
    parseInt(firstDateParts[1]) - 1,
    parseInt(firstDateParts[2])
  )
  firstDate.setHours(0, 0, 0, 0)
  
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  const daysDiff = Math.floor((today - firstDate) / (1000 * 60 * 60 * 24)) + 1
  
  if (daysDiff <= 0) {
    return {
      rate: 0,
      completedDays: completedDates.length,
      totalDays: 0
    }
  }
  
  const rate = (completedDates.length / daysDiff) * 100
  return {
    rate: Math.min(Math.round(rate), 100),
    completedDays: completedDates.length,
    totalDays: daysDiff
  }
}

export function getRecentRecords(habit, days = 365) {
  const records = habit.records || {}
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const result = []
  
  for (let i = days - 1; i >= 0; i--) {
    const date = new Date(today)
    date.setDate(date.getDate() - i)
    const dateStr = getTodayStringFromDate(date)
    result.push({
      date: dateStr,
      completed: records[dateStr] === true
    })
  }
  
  return result
}

// 计算单个习惯的历史最长连续天数
export function calculateLongestStreakFromRecords(habit) {
  const records = habit.records || {}
  const completedDates = Object.keys(records)
    .filter(date => records[date] === true)
    .sort()

  if (completedDates.length === 0) {
    return 0
  }

  let longest = 1
  let current = 1
  const msPerDay = 1000 * 60 * 60 * 24

  for (let i = 1; i < completedDates.length; i++) {
    const prev = new Date(completedDates[i - 1])
    const curr = new Date(completedDates[i])
    prev.setHours(0, 0, 0, 0)
    curr.setHours(0, 0, 0, 0)
    const diffDays = Math.round((curr - prev) / msPerDay)

    if (diffDays === 1) {
      current++
    } else {
      longest = Math.max(longest, current)
      current = 1
    }
  }

  return Math.max(longest, current)
}

export function calculateStats(habits) {
  const activeHabits = habits.filter(h => !h.archived)
  const today = getTodayString()
  
  const todayCompleted = activeHabits.filter(h => isCompletedToday(h)).length
  const todayTotal = activeHabits.length
  const todayRate = todayTotal > 0 ? Math.round((todayCompleted / todayTotal) * 100) : 0
  
  // 所有习惯中「单个习惯」历史最长连续天数及对应习惯名称
  let longestStreak = 0
  let longestStreakHabitName = ''
  if (activeHabits.length > 0) {
    activeHabits.forEach(habit => {
      const streak = calculateLongestStreakFromRecords(habit)
      if (streak > longestStreak) {
        longestStreak = streak
        longestStreakHabitName = habit.name || ''
      }
    })
  }
  
  return {
    todayCompleted,
    todayTotal,
    todayRate,
    longestStreak,
    longestStreakHabitName
  }
}
