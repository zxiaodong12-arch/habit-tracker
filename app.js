// 习惯追踪器核心逻辑

class HabitTracker {
    constructor() {
        this.habits = this.loadHabits();
        this.currentHabitId = null;
        this.editingHabitId = null; // 当前正在编辑的习惯ID（用于修改名称/emoji/颜色）
        this.archivedCollapsed = true; // 已归档列表是否折叠
        this.init();
    }

    init() {
        this.renderHabits();
        this.renderArchivedHabits();
        this.updateStats();
        this.setupEventListeners();
    }

    // 加载习惯数据
    loadHabits() {
        const stored = localStorage.getItem('habits');
        if (stored) {
            const habits = JSON.parse(stored);
            // 确保每个习惯都有记录数据
            habits.forEach(habit => {
                if (!habit.records) {
                    habit.records = {};
                }
                // 兼容旧数据：如果没有颜色或表情，补默认值
                if (!habit.color) {
                    habit.color = '#10b981';
                }
                if (!habit.emoji) {
                    habit.emoji = '📝';
                }
            });
            return habits;
        }
        // 首次使用时，提供一些默认示例习惯
        const today = new Date().toISOString();
        const defaultHabits = [
            {
                id: (Date.now() - 3).toString(),
                name: '喝八杯水',
                emoji: '💧',
                color: '#0ea5e9',
                records: {},
                archived: false,
                createdAt: today
            },
            {
                id: (Date.now() - 2).toString(),
                name: '跑步五公里',
                emoji: '🏃‍♂️',
                color: '#f97316',
                records: {},
                archived: false,
                createdAt: today
            },
            {
                id: (Date.now() - 1).toString(),
                name: '阅读30分钟',
                emoji: '📚',
                color: '#a855f7',
                records: {},
                archived: false,
                createdAt: today
            }
        ];
        return defaultHabits;
    }

    // 保存习惯数据
    saveHabits() {
        localStorage.setItem('habits', JSON.stringify(this.habits));
        this.renderHabits();
        this.renderArchivedHabits();
        this.updateStats();
    }

    // 获取今天的日期字符串 (YYYY-MM-DD)
    getTodayString() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    

    // 检查习惯今天是否完成
    isCompletedToday(habit) {
        const today = this.getTodayString();
        return habit.records[today] === true;
    }

    // 切换今天的打卡状态
    toggleHabit(habitId) {
        const habit = this.habits.find(h => h.id === habitId);
        if (!habit) return;

        const today = this.getTodayString();
        if (habit.records[today]) {
            delete habit.records[today];
        } else {
            habit.records[today] = true;
        }

        this.saveHabits();
    }

    // 计算连续天数
    calculateStreak(habit) {
        const records = habit.records || {};
        
        if (Object.keys(records).length === 0) return 0;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = this.getTodayString(); // 使用统一的日期字符串函数
        
        // 检查今天是否完成
        const todayCompleted = records[todayStr] === true;
        
        // 确定起始日期：如果今天完成了从今天开始，否则从昨天开始
        let checkDate = new Date(today);
        if (!todayCompleted) {
            checkDate.setDate(checkDate.getDate() - 1);
        }
        
        let streak = 0;
        
        // 从起始日期开始往前数连续完成的天数
        while (true) {
            const dateStr = this.getTodayStringFromDate(checkDate); // 使用统一的日期字符串函数
            
            if (records[dateStr] === true) {
                streak++;
                // 继续往前一天
                checkDate.setDate(checkDate.getDate() - 1);
            } else {
                // 如果中断了，停止计数
                break;
            }
        }
        
        return streak;
    }
    
    // 从Date对象获取日期字符串（与getTodayString格式一致）
    getTodayStringFromDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // 计算最长连续天数
    calculateLongestStreak(habit) {
        const records = habit.records || {};
        const dates = Object.keys(records)
            .filter(date => records[date] === true)
            .map(date => {
                // 解析日期字符串为本地日期
                const parts = date.split('-');
                return new Date(
                    parseInt(parts[0]),
                    parseInt(parts[1]) - 1,
                    parseInt(parts[2])
                );
            })
            .sort((a, b) => a - b);

        if (dates.length === 0) return 0;

        let longestStreak = 0;
        let currentStreak = 1;

        for (let i = 1; i < dates.length; i++) {
            const prevDate = dates[i - 1];
            const currDate = dates[i];
            const diffDays = Math.floor((currDate - prevDate) / (1000 * 60 * 60 * 24));

            if (diffDays === 1) {
                currentStreak++;
            } else {
                longestStreak = Math.max(longestStreak, currentStreak);
                currentStreak = 1;
            }
        }

        return Math.max(longestStreak, currentStreak);
    }

    // 计算完成率
    calculateCompletionRate(habit) {
        const info = this.getCompletionRateInfo(habit);
        return info.rate;
    }
    
    // 获取完成率详细信息
    getCompletionRateInfo(habit) {
        const records = habit.records || {};
        const completedDates = Object.keys(records)
            .filter(date => records[date] === true)
            .sort(); // 按日期字符串排序，确保找到最早的日期
        
        if (completedDates.length === 0) {
            return {
                rate: 0,
                completedDays: 0,
                totalDays: 0
            };
        }

        // 从第一次打卡日期到今天的天数
        // 解析日期字符串为本地日期（已排序，第一个就是最早的）
        const firstDateParts = completedDates[0].split('-');
        const firstDate = new Date(
            parseInt(firstDateParts[0]),
            parseInt(firstDateParts[1]) - 1,
            parseInt(firstDateParts[2])
        );
        firstDate.setHours(0, 0, 0, 0);
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // 计算从第一次打卡到今天（包括今天）的总天数
        const daysDiff = Math.floor((today - firstDate) / (1000 * 60 * 60 * 24)) + 1;
        
        if (daysDiff <= 0) {
            return {
                rate: 0,
                completedDays: completedDates.length,
                totalDays: 0
            };
        }
        
        // 确保完成率不超过100%
        const rate = (completedDates.length / daysDiff) * 100;
        return {
            rate: Math.min(Math.round(rate), 100),
            completedDays: completedDates.length,
            totalDays: daysDiff
        };
    }

    // 获取最近N天的记录（用于热力图）
    getRecentRecords(habit, days = 365) {
        const records = habit.records || {};
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const result = [];

        for (let i = days - 1; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            const dateStr = this.getTodayStringFromDate(date);
            result.push({
                date: dateStr,
                completed: records[dateStr] === true
            });
        }

        return result;
    }

    // 渲染习惯列表
    renderHabits() {
        const container = document.getElementById('habits-container');
        const activeHabits = this.habits.filter(h => !h.archived);
        
        if (activeHabits.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <h3>还没有习惯</h3>
                    <p>点击右下角按钮添加你的第一个习惯</p>
                </div>
            `;
            return;
        }

        container.innerHTML = activeHabits
            .map(habit => {
                const completed = this.isCompletedToday(habit);
                const streak = this.calculateStreak(habit);
                const recentRecords = this.getRecentRecords(habit, 56); // 最近8周
                
                return `
                    <div class="habit-card ${completed ? 'completed' : ''}" 
                         data-habit-id="${habit.id}"
                         style="--habit-color: ${habit.color};">
                        <div class="habit-header">
                            <div class="habit-info">
                                <div class="habit-emoji" style="background: ${habit.color}20; color: ${habit.color}">
                                    ${habit.emoji || '📝'}
                                </div>
                                <div class="habit-details">
                                    <div class="habit-name">${habit.name}</div>
                                    <div class="habit-streak">
                                        连续 <strong>${streak}</strong> 天
                                    </div>
                                </div>
                            </div>
                            <div class="checkbox-wrapper">
                                <div class="habit-checkbox ${completed ? 'checked' : ''}" 
                                     data-habit-id="${habit.id}"></div>
                            </div>
                        </div>
                        <div class="heatmap-preview">
                            <div class="heatmap-preview-title">最近8周</div>
                            <div class="heatmap-grid">
                                ${recentRecords.map(record => `
                                    <div class="heatmap-day ${record.completed ? 'completed level-3' : ''}" 
                                         title="${record.date}: ${record.completed ? '已完成' : '未完成'}"></div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `;
            })
            .join('');

        // 绑定点击事件
        container.querySelectorAll('.habit-checkbox').forEach(checkbox => {
            checkbox.addEventListener('click', (e) => {
                e.stopPropagation();
                const habitId = checkbox.dataset.habitId;
                this.toggleHabit(habitId);
            });
        });

        // 绑定卡片点击事件（查看详情）
        container.querySelectorAll('.habit-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.habit-checkbox')) {
                    const habitId = card.dataset.habitId;
                    this.showHabitDetail(habitId);
                }
            });
        });
    }

    // 渲染归档习惯列表
    renderArchivedHabits() {
        const section = document.getElementById('archived-section');
        const container = document.getElementById('archived-habits-container');
        const countEl = document.getElementById('archived-count');
        const toggleBtn = document.getElementById('archived-toggle-btn');
        if (!section || !container || !countEl) return;

        const archivedHabits = this.habits.filter(habit => habit.archived);

        if (archivedHabits.length === 0) {
            section.style.display = 'none';
            container.innerHTML = '';
            countEl.textContent = '';
            return;
        }

        section.style.display = '';
        countEl.textContent = `${archivedHabits.length} 个`;

        // 根据折叠状态更新样式和按钮文案
        if (this.archivedCollapsed) {
            section.classList.add('collapsed');
            if (toggleBtn) toggleBtn.textContent = '展开';
        } else {
            section.classList.remove('collapsed');
            if (toggleBtn) toggleBtn.textContent = '收起';
        }

        container.innerHTML = archivedHabits
            .map(habit => {
                const info = this.getCompletionRateInfo(habit);
                const createdDate = habit.createdAt
                    ? habit.createdAt.split('T')[0]
                    : '';

                return `
                    <div class="habit-card archived" data-habit-id="${habit.id}" style="--habit-color: ${habit.color};">
                        <div class="habit-header">
                            <div class="habit-info">
                                <div class="habit-emoji" style="background: ${habit.color}08; color: ${habit.color}">
                                    ${habit.emoji || '📝'}
                                </div>
                                <div class="habit-details">
                                    <div class="habit-name">${habit.name}</div>
                                    <div class="habit-meta">
                                        <span>完成 ${info.completedDays} 天</span>
                                        ${createdDate ? `<span> · 创建于 ${createdDate}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <span class="habit-badge">已归档</span>
                        </div>
                    </div>
                `;
            })
            .join('');

        // 点击卡片查看详情
        container.querySelectorAll('.habit-card').forEach(card => {
            card.addEventListener('click', () => {
                const habitId = card.dataset.habitId;
                this.showHabitDetail(habitId);
            });
        });
    }

    // 更新统计信息
    updateStats() {
        const activeHabits = this.habits.filter(h => !h.archived);
        const today = this.getTodayString();
        
        // 今日完成数
        const todayCompleted = activeHabits.filter(h => this.isCompletedToday(h)).length;
        document.getElementById('today-completion').textContent = `${todayCompleted}/${activeHabits.length}`;

        // 总完成率（今日完成率）
        if (activeHabits.length > 0) {
            const todayCompletedCount = activeHabits.filter(h => this.isCompletedToday(h)).length;
            const totalCompletion = Math.round((todayCompletedCount / activeHabits.length) * 100);
            document.getElementById('total-completion').textContent = `${totalCompletion}%`;
        } else {
            document.getElementById('total-completion').textContent = '0%';
        }

        // 最长连续天数
        const longestStreak = activeHabits.reduce((max, habit) => {
            return Math.max(max, this.calculateLongestStreak(habit));
        }, 0);
        document.getElementById('longest-streak').textContent = `${longestStreak}天`;
    }

    // 显示习惯详情
    showHabitDetail(habitId) {
        const habit = this.habits.find(h => h.id === habitId);
        if (!habit) return;

        this.currentHabitId = habitId;
        const modal = document.getElementById('habit-detail-modal');
        const archiveBtn = document.getElementById('archive-habit-btn');
        // 将当前习惯颜色注入详情模态框，供样式使用
        if (habit.color) {
            modal.style.setProperty('--habit-color', habit.color);
        } else {
            modal.style.removeProperty('--habit-color');
        }
        
        // 更新详情内容
        document.getElementById('detail-habit-name').textContent = habit.name;
        document.getElementById('detail-current-streak').textContent = `${this.calculateStreak(habit)}天`;
        document.getElementById('detail-longest-streak').textContent = `${this.calculateLongestStreak(habit)}天`;
        
        // 更新总完成率及说明
        const completionInfo = this.getCompletionRateInfo(habit);
        document.getElementById('detail-completion-rate').textContent = `${completionInfo.rate}%`;
        if (completionInfo.totalDays > 0) {
            // 格式化第一次打卡日期
            const firstDateParts = Object.keys(habit.records || {})
                .filter(date => habit.records[date] === true)
                .sort()[0]?.split('-') || [];
            const firstDateStr = firstDateParts.length > 0 
                ? `${firstDateParts[0]}-${firstDateParts[1]}-${firstDateParts[2]}`
                : '';
            
            document.getElementById('detail-completion-hint').innerHTML = `
                <div style="line-height: 1.4;">
                    <div>完成：${completionInfo.completedDays}/${completionInfo.totalDays} 天</div>
                    ${firstDateStr ? `<div style="font-size: 10px; margin-top: 2px; opacity: 0.7;">首日：${firstDateStr}</div>` : ''}
                    <div style="font-size: 10px; margin-top: 2px; opacity: 0.7;">范围：首日 ~ 今天</div>
                </div>
            `;
        } else {
            document.getElementById('detail-completion-hint').textContent = '';
        }

        // 更新归档按钮文案
        if (habit.archived) {
            archiveBtn.textContent = '恢复习惯';
        } else {
            archiveBtn.textContent = '归档习惯';
        }

        // 渲染完整热力图（最近30天）
        const heatmapContainer = document.getElementById('detail-heatmap');
        const recentRecords = this.getRecentRecords(habit, 30); // 最近30天

        // 格式化日期显示
        const formatDate = (dateStr) => {
            const parts = dateStr.split('-');
            const date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
            const weekday = weekdays[date.getDay()];
            return `${parts[0]}-${parts[1]}-${parts[2]} ${weekday}`;
        };

        const completedCount = recentRecords.filter(r => r.completed).length;

        heatmapContainer.innerHTML = `
            <div class="heatmap-header">
                <div class="heatmap-header-top">
                    <span class="heatmap-title">最近30天</span>
                    <span class="heatmap-summary">完成 ${completedCount} 天</span>
                </div>
                <div class="heatmap-header-bottom">
                    <div class="heatmap-legend-item">
                        <span class="heatmap-legend-box heatmap-legend-empty"></span>
                        <span>未完成</span>
                    </div>
                    <div class="heatmap-legend-item">
                        <span class="heatmap-legend-box heatmap-legend-done"></span>
                        <span>已完成</span>
                    </div>
                </div>
            </div>
            <div class="heatmap-grid-dates">
                ${recentRecords.map(record => {
                    const dateObj = new Date(record.date);
                    const day = dateObj.getDate();
                    const isToday = record.date === this.getTodayString();
                    const statusText = record.completed ? '✅ 已完成' : '❌ 未完成';
                    const classes = [
                        'heatmap-date-cell',
                        record.completed ? 'completed' : '',
                        isToday ? 'today' : ''
                    ].filter(Boolean).join(' ');
                    return `
                        <div class="${classes}" 
                             title="${formatDate(record.date)} - ${statusText}">
                            <span class="heatmap-date-number">${day}</span>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        modal.classList.add('active');
    }

    // 添加新习惯
    addHabit(name, emoji, color) {
        const habit = {
            id: Date.now().toString(),
            name: name.trim(),
            emoji: emoji || '📝',
            color: color || '#10b981',
            records: {},
            archived: false,
            createdAt: new Date().toISOString()
        };

        this.habits.push(habit);
        this.saveHabits();
    }

    // 归档习惯
    archiveHabit(habitId) {
        const habit = this.habits.find(h => h.id === habitId);
        if (habit) {
            habit.archived = true;
            this.saveHabits();
        }
    }

    // 切换归档状态（归档 / 恢复）
    toggleArchiveHabit(habitId) {
        const habit = this.habits.find(h => h.id === habitId);
        if (!habit) return;
        habit.archived = !habit.archived;
        this.saveHabits();
    }

    // 删除习惯
    deleteHabit(habitId) {
        if (confirm('确定要删除这个习惯吗？删除后数据将无法恢复。')) {
            this.habits = this.habits.filter(h => h.id !== habitId);
            this.saveHabits();
            this.closeHabitDetail();
        }
    }

    // 关闭习惯详情
    closeHabitDetail() {
        const modal = document.getElementById('habit-detail-modal');
        modal.classList.remove('active');
        this.currentHabitId = null;
    }

    // 导出数据
    exportData() {
        const data = {
            habits: this.habits,
            exportDate: new Date().toISOString(),
            version: '1.0'
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `habit-tracker-${this.getTodayString()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // 导入数据
    importData(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const data = JSON.parse(e.target.result);
                if (data.habits && Array.isArray(data.habits)) {
                    if (confirm('导入数据将覆盖当前所有习惯，确定要继续吗？')) {
                        this.habits = data.habits;
                        this.saveHabits();
                        alert('导入成功！');
                    }
                } else {
                    alert('文件格式不正确');
                }
            } catch (error) {
                alert('导入失败：' + error.message);
            }
        };
        reader.readAsText(file);
    }

    // 设置事件监听
    setupEventListeners() {
        // 添加习惯按钮
        document.getElementById('add-habit-btn').addEventListener('click', () => {
            this.editingHabitId = null; // 新建模式
            document.getElementById('add-habit-modal').classList.add('active');
            document.querySelector('#add-habit-modal .modal-header h2').textContent = '添加新习惯';
            document.querySelector('#add-habit-form .btn-primary').textContent = '添加';
            document.getElementById('habit-name').focus();
        });

        // 关闭添加习惯模态框
        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('add-habit-modal').classList.remove('active');
            document.getElementById('add-habit-form').reset();
        });

        document.getElementById('cancel-btn').addEventListener('click', () => {
            document.getElementById('add-habit-modal').classList.remove('active');
            document.getElementById('add-habit-form').reset();
        });

        // 添加习惯表单提交
        document.getElementById('add-habit-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('habit-name').value;
            const emoji = document.getElementById('habit-emoji').value;
            const color = document.getElementById('habit-color').value;
            
            if (name.trim()) {
                if (this.editingHabitId) {
                    // 编辑已有习惯
                    const habit = this.habits.find(h => h.id === this.editingHabitId);
                    if (habit) {
                        habit.name = name.trim();
                        habit.emoji = emoji || '📝';
                        habit.color = color || '#10b981';
                        this.saveHabits();
                    }
                } else {
                    // 新建习惯
                    this.addHabit(name, emoji, color);
                }
                document.getElementById('add-habit-modal').classList.remove('active');
                document.getElementById('add-habit-form').reset();
                this.editingHabitId = null;
            }
        });

        // 关闭习惯详情模态框
        document.getElementById('close-detail-modal').addEventListener('click', () => {
            this.closeHabitDetail();
        });

        // 编辑习惯信息
        document.getElementById('edit-habit-info-btn').addEventListener('click', () => {
            if (!this.currentHabitId) return;
            const habit = this.habits.find(h => h.id === this.currentHabitId);
            if (!habit) return;

            this.editingHabitId = habit.id;

            // 预填表单
            document.getElementById('habit-name').value = habit.name || '';
            document.getElementById('habit-emoji').value = habit.emoji || '';
            document.getElementById('habit-color').value = habit.color || '#10b981';

            // 调整标题和按钮文案
            document.querySelector('#add-habit-modal .modal-header h2').textContent = '编辑习惯';
            document.querySelector('#add-habit-form .btn-primary').textContent = '保存';

            // 打开编辑弹窗，关闭详情
            this.closeHabitDetail();
            document.getElementById('add-habit-modal').classList.add('active');
            document.getElementById('habit-name').focus();
        });

        // 已归档习惯折叠/展开
        const archivedToggleBtn = document.getElementById('archived-toggle-btn');
        if (archivedToggleBtn) {
            archivedToggleBtn.addEventListener('click', () => {
                this.archivedCollapsed = !this.archivedCollapsed;
                this.renderArchivedHabits();
            });
        }

        // 归档习惯
        document.getElementById('archive-habit-btn').addEventListener('click', () => {
            if (this.currentHabitId) {
                const habit = this.habits.find(h => h.id === this.currentHabitId);
                if (!habit) return;
                const isArchived = habit.archived;
                const actionText = isArchived ? '恢复' : '归档';
                const message = isArchived
                    ? '确定要恢复这个习惯吗？恢复后它会重新出现在主列表中。'
                    : '确定要归档这个习惯吗？归档后它将不再显示在主列表中，但数据会保留。';
                if (confirm(message)) {
                    this.toggleArchiveHabit(this.currentHabitId);
                    this.closeHabitDetail();
                }
            }
        });

        // 删除习惯
        document.getElementById('delete-habit-btn').addEventListener('click', () => {
            if (this.currentHabitId) {
                this.deleteHabit(this.currentHabitId);
            }
        });

        // 导出数据
        document.getElementById('export-btn').addEventListener('click', () => {
            this.exportData();
        });

        // 导入数据
        document.getElementById('import-btn').addEventListener('click', () => {
            document.getElementById('import-file').click();
        });

        document.getElementById('import-file').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.importData(file);
                e.target.value = ''; // 重置input
            }
        });

        // 点击模态框背景关闭
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    if (modal.id === 'habit-detail-modal') {
                        this.closeHabitDetail();
                    } else {
                        document.getElementById('add-habit-form').reset();
                    }
                }
            });
        });
    }
}

// 初始化应用
document.addEventListener('DOMContentLoaded', () => {
    window.habitTracker = new HabitTracker();
});
