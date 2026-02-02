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

export function calculateStats(habits) {
  const activeHabits = habits.filter(h => !h.archived)
  const today = getTodayString()
  
  const todayCompleted = activeHabits.filter(h => isCompletedToday(h)).length
  const todayTotal = activeHabits.length
  const todayRate = todayTotal > 0 ? Math.round((todayCompleted / todayTotal) * 100) : 0
  
  const longestStreak = activeHabits.length > 0
    ? Math.max(...activeHabits.map(h => calculateStreak(h)))
    : 0
  
  return {
    todayCompleted,
    todayTotal,
    todayRate,
    longestStreak
  }
}
