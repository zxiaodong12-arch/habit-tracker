<?php

namespace app\controller;

use app\BaseController;
use app\model\Habit as HabitModel;
use think\facade\Db;
use think\Request;

class Habits extends BaseController
{
    /**
     * 获取习惯列表
     */
    public function index(Request $request)
    {
        try {
            // 从 token 获取用户ID，如果没有则从参数获取（兼容旧代码）
            $userId = $this->getUserIdFromToken($request) ?? $request->param('user_id');
            $archived = $request->param('archived');

            $where = [];
            if ($userId !== null) {
                $where['user_id'] = $userId;
            }
            if ($archived !== null) {
                $where['archived'] = $archived === 'true' ? 1 : 0;
            }

            $habits = HabitModel::where($where)
                ->order('created_at', 'desc')
                ->select();

            return json([
                'success' => true,
                'data' => $habits
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取习惯列表失败',
                'error' => $e->getMessage() . $e->getFile() . $e->getLine()
            ], 500);
        }
    }

    /**
     * 获取单个习惯详情
     */
    public function read($id)
    {
        try {
            $habit = HabitModel::find($id);

            if (!$habit) {
                return json([
                    'success' => false,
                    'message' => '习惯不存在'
                ], 404);
            }

            return json([
                'success' => true,
                'data' => $habit
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                // 为了前端调试，直接把具体异常信息拼到 message 里
                'message' => '获取习惯详情失败: ' . $e->getMessage(),
                'error' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * 创建新习惯
     */
    public function save(Request $request)
    {
        try {
            $data = $request->post();

            if (empty($data['name'])) {
                return json([
                    'success' => false,
                    'message' => '习惯名称不能为空'
                ], 400);
            }

            // 从 token 获取用户ID，如果没有则从参数获取（兼容旧代码）
            $userId = $this->getUserIdFromToken($request) ?? $data['user_id'] ?? null;

            // 如果仍然没有 userId，返回未登录错误，避免写入 null
            if ($userId === null) {
                return json([
                    'success' => false,
                    'message' => '未登录或 token 无效，无法创建习惯'
                ], 401);
            }

            $habit = new HabitModel();
            $habit->user_id = $userId;
            $habit->name = $data['name'];
            $habit->emoji = $data['emoji'] ?? '📝';
            $habit->color = $data['color'] ?? '#10b981';
            $habit->archived = isset($data['archived']) && $data['archived'] ? 1 : 0;
            // 目标设置
            $habit->target_type = $data['target_type'] ?? 'daily';
            $habit->target_count = isset($data['target_count']) ? (int)$data['target_count'] : 1;
            $habit->target_start_date = isset($data['target_start_date']) ? $data['target_start_date'] : date('Y-m-d');
            $habit->save();

            return json([
                'success' => true,
                'data' => $habit
            ], 201);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '创建习惯失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 从 token 获取用户ID（与 Auth 控制器中的实现保持一致）
     */
    private function getUserIdFromToken(Request $request)
    {
        // 从请求头获取 token
        $token = $request->header('Authorization');
        if ($token) {
            // 移除 "Bearer " 前缀（如果存在）
            $token = str_replace('Bearer ', '', $token);
        } else {
            // 尝试从请求参数获取
            $token = $request->param('token');
        }

        if (!$token) {
            return null;
        }

        try {
            // 解码 token
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);

            if (count($parts) !== 3) {
                return null;
            }

            $userId = $parts[0];
            $timestamp = $parts[1];
            $hash = $parts[2];

            // 验证 token（简单验证，生产环境需要更严格的验证）
            $expectedHash = md5($userId . $timestamp . 'habit-tracker-secret-key');
            if ($hash !== $expectedHash) {
                return null;
            }

            // 检查 token 是否过期（7天）
            if (time() - $timestamp > 7 * 24 * 60 * 60) {
                return null;
            }

            return (int)$userId;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 更新习惯
     */
    public function update(Request $request, $id)
    {
        try {
            $habit = HabitModel::find($id);

            if (!$habit) {
                return json([
                    'success' => false,
                    'message' => '习惯不存在'
                ], 404);
            }

            $data = $request->put();

            if (isset($data['name'])) {
                $habit->name = $data['name'];
            }
            if (isset($data['emoji'])) {
                $habit->emoji = $data['emoji'];
            }
            if (isset($data['color'])) {
                $habit->color = $data['color'];
            }
            if (isset($data['archived'])) {
                $habit->archived = $data['archived'] ? 1 : 0;
            }
            // 目标设置
            if (isset($data['target_type'])) {
                $habit->target_type = $data['target_type'];
            }
            if (isset($data['target_count'])) {
                $habit->target_count = (int)$data['target_count'];
            }
            if (isset($data['target_start_date'])) {
                $habit->target_start_date = $data['target_start_date'];
            }

            $habit->save();

            return json([
                'success' => true,
                'data' => $habit
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '更新习惯失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 删除习惯
     */
    public function delete($id)
    {
        try {
            $habit = HabitModel::find($id);

            if (!$habit) {
                return json([
                    'success' => false,
                    'message' => '习惯不存在'
                ], 404);
            }

            $habit->delete();

            return json([
                'success' => true,
                'message' => '习惯删除成功'
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '删除习惯失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 获取习惯详情（包含统计数据、热力图、趋势等）
     */
    public function detail($id)
    {
        try {
            $habit = HabitModel::find($id);

            if (!$habit) {
                return json([
                    'success' => false,
                    'message' => '习惯不存在'
                ], 404);
            }

            // 获取所有打卡记录
            $allRecords = Db::table('habit_records')
                ->where('habit_id', $id)
                ->order('record_date', 'asc')
                ->select()
                ->toArray();

            // 根据目标类型计算不同的统计数据
            $targetType = $habit->target_type ?? 'daily';
            $stats = $this->calculateStatsByTargetType($habit, $allRecords, $targetType);

            // 计算目标进度
            $targetProgress = $this->calculateTargetProgress($habit, $allRecords);

            // 根据目标类型生成不同的视图数据
            $targetType = $habit->target_type ?? 'daily';
            $viewData = $this->generateViewDataByTargetType($targetType, $allRecords, $habit->created_at);

            // 所有记录（倒序，前端会做分页）
            $recentRecords = array_reverse($allRecords);

            return json([
                'success' => true,
                'data' => [
                    'habit' => $habit,
                    'stats' => $stats,
                    'target_progress' => $targetProgress,
                    'view_data' => $viewData,
                    'recent_records' => $recentRecords,
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取习惯详情失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 计算当前连续天数
     */
    private function calculateCurrentStreak($records)
    {
        $streak = 0;
        $today = date('Y-m-d');
        $checkDate = new \DateTime($today);

        // 检查今天是否完成
        $todayRecord = array_filter($records, function($r) use ($today) {
            return $r['record_date'] === $today && $r['completed'] == 1;
        });

        if (empty($todayRecord)) {
            $checkDate->modify('-1 day');
        }

        while (true) {
            $dateStr = $checkDate->format('Y-m-d');
            $record = array_filter($records, function($r) use ($dateStr) {
                return $r['record_date'] === $dateStr && $r['completed'] == 1;
            });

            if (!empty($record)) {
                $streak++;
                $checkDate->modify('-1 day');
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * 计算最长连续天数
     */
    private function calculateLongestStreak($records)
    {
        $completedRecords = array_values(array_filter($records, function($r) {
            return $r['completed'] == 1;
        }));

        if (empty($completedRecords)) {
            return 0;
        }

        usort($completedRecords, function($a, $b) {
            return strcmp($a['record_date'], $b['record_date']);
        });

        $longestStreak = 0;
        $currentStreak = 1;

        for ($i = 1; $i < count($completedRecords); $i++) {
            $prevDate = new \DateTime($completedRecords[$i - 1]['record_date']);
            $currDate = new \DateTime($completedRecords[$i]['record_date']);
            $diffDays = $currDate->diff($prevDate)->days;

            if ($diffDays === 1) {
                $currentStreak++;
            } else {
                $longestStreak = max($longestStreak, $currentStreak);
                $currentStreak = 1;
            }
        }

        return max($longestStreak, $currentStreak);
    }

    /**
     * 计算目标进度
     */
    private function calculateTargetProgress($habit, $allRecords)
    {
        if (!$habit->target_type || !$habit->target_count) {
            return null;
        }

        $today = new \DateTime();
        $startDate = $habit->target_start_date ? new \DateTime($habit->target_start_date) : new \DateTime($habit->created_at);

        // 根据目标类型计算周期
        $periodStart = null;
        $periodEnd = null;
        $completed = 0;

        switch ($habit->target_type) {
            case 'daily':
                $periodStart = clone $today;
                $periodEnd = clone $today;
                break;
            case 'weekly':
                // 本周一
                $periodStart = clone $today;
                $periodStart->modify('monday this week');
                $periodEnd = clone $periodStart;
                $periodEnd->modify('+6 days');
                break;
            case 'monthly':
                // 本月第一天
                $periodStart = new \DateTime($today->format('Y-m-01'));
                $periodEnd = new \DateTime($today->format('Y-m-t'));
                break;
            case 'yearly':
                // 今年第一天
                $periodStart = new \DateTime($today->format('Y-01-01'));
                $periodEnd = new \DateTime($today->format('Y-12-31'));
                break;
        }

        if ($periodStart && $periodEnd) {
            $periodStartStr = $periodStart->format('Y-m-d');
            $periodEndStr = $periodEnd->format('Y-m-d');

            foreach ($allRecords as $record) {
                if ($record['record_date'] >= $periodStartStr && 
                    $record['record_date'] <= $periodEndStr && 
                    $record['completed'] == 1) {
                    $completed++;
                }
            }

            $progress = $habit->target_count > 0 ? round(($completed / $habit->target_count) * 100) : 0;
            $remaining = max(0, $habit->target_count - $completed);

            // 计算剩余天数
            $remainingDays = 0;
            if ($habit->target_type === 'weekly') {
                $remainingDays = max(0, $today->diff($periodEnd)->days);
            } elseif ($habit->target_type === 'monthly') {
                $remainingDays = max(0, $today->diff($periodEnd)->days);
            } elseif ($habit->target_type === 'yearly') {
                $remainingDays = max(0, $today->diff($periodEnd)->days);
            }

            return [
                'target_type' => $habit->target_type,
                'target_count' => $habit->target_count,
                'completed' => $completed,
                'remaining' => $remaining,
                'progress' => min(100, $progress),
                'remaining_days' => $remainingDays,
                'period_start' => $periodStartStr,
                'period_end' => $periodEndStr,
            ];
        }

        return null;
    }

    /**
     * 生成热力图数据
     */
    private function generateHeatmap($records, $createdAt)
    {
        $heatmap = [];
        $startDate = new \DateTime($createdAt);
        $today = new \DateTime();
        $endDate = clone $today;

        // 最多显示一年
        $oneYearAgo = clone $today;
        $oneYearAgo->modify('-1 year');
        if ($startDate < $oneYearAgo) {
            $startDate = $oneYearAgo;
        }

        $currentDate = clone $startDate;
        $recordsMap = [];
        foreach ($records as $record) {
            $recordsMap[$record['record_date']] = $record['completed'];
        }

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $completed = isset($recordsMap[$dateStr]) ? (int)$recordsMap[$dateStr] : 0;
            
            $heatmap[] = [
                'date' => $dateStr,
                'completed' => $completed,
                'level' => $completed ? 3 : 0, // 0-4 级别，用于颜色深浅
            ];

            $currentDate->modify('+1 day');
        }

        return $heatmap;
    }

    /**
     * 生成月度趋势数据
     */
    private function generateMonthlyTrend($records)
    {
        $monthlyData = [];
        $recordsMap = [];

        foreach ($records as $record) {
            $month = substr($record['record_date'], 0, 7); // YYYY-MM
            if (!isset($recordsMap[$month])) {
                $recordsMap[$month] = ['completed' => 0, 'total' => 0];
            }
            $recordsMap[$month]['total']++;
            if ($record['completed'] == 1) {
                $recordsMap[$month]['completed']++;
            }
        }

        foreach ($recordsMap as $month => $data) {
            $monthlyData[] = [
                'month' => $month,
                'completed' => $data['completed'],
                'total' => $data['total'],
                'rate' => $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0,
            ];
        }

        usort($monthlyData, function($a, $b) {
            return strcmp($a['month'], $b['month']);
        });

        return $monthlyData;
    }

    /**
     * 归档/恢复习惯
     */
    public function archive(Request $request, $id)
    {
        try {
            $habit = HabitModel::find($id);

            if (!$habit) {
                return json([
                    'success' => false,
                    'message' => '习惯不存在'
                ], 404);
            }

            $data = $request->put();

            if (!isset($data['archived'])) {
                return json([
                    'success' => false,
                    'message' => 'archived 参数必填'
                ], 400);
            }

            $habit->archived = $data['archived'] ? 1 : 0;
            $habit->save();

            return json([
                'success' => true,
                'data' => $habit
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '操作失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 根据目标类型计算统计数据
     */
    private function calculateStatsByTargetType($habit, $allRecords, $targetType)
    {
        $today = new \DateTime();
        $totalRecords = count($allRecords);
        $completedRecords = array_filter($allRecords, function($r) {
            return $r['completed'] == 1;
        });
        $completedCount = count($completedRecords);

        switch ($targetType) {
            case 'daily':
                // 每天完成：统计天数
                $totalPeriods = 0;
                if (!empty($allRecords)) {
                    $earliestDate = null;
                    foreach ($allRecords as $record) {
                        $recordDate = new \DateTime($record['record_date']);
                        if ($earliestDate === null || $recordDate < $earliestDate) {
                            $earliestDate = $recordDate;
                        }
                    }
                    if ($earliestDate) {
                        $totalPeriods = $today->diff($earliestDate)->days + 1;
                    }
                }
                $completionRate = $totalPeriods > 0 ? round(($completedCount / $totalPeriods) * 100) : 0;
                $currentStreak = $this->calculateCurrentStreak($allRecords);
                $longestStreak = $this->calculateLongestStreak($allRecords);
                
                return [
                    'target_type' => 'daily',
                    'total_periods' => $totalPeriods,
                    'total_periods_label' => '总天数',
                    'completed_periods' => $completedCount,
                    'completed_periods_label' => '已完成天数',
                    'completion_rate' => $completionRate,
                    'completion_rate_desc' => '已完成数 / 总天数',
                    'current_streak' => $currentStreak,
                    'current_streak_label' => '连续天数',
                    'current_streak_desc' => '当前连续完成天数',
                    'longest_streak' => $longestStreak,
                    'longest_streak_label' => '最长连续',
                    'longest_streak_desc' => '历史最长连续天数',
                    'total_records' => $totalRecords,
                ];

            case 'weekly':
                // 每周完成：统计周数
                $totalWeeks = 0;
                $completedWeeks = 0;
                $weekMap = [];
                
                if (!empty($allRecords)) {
                    $earliestDate = null;
                    foreach ($allRecords as $record) {
                        $recordDate = new \DateTime($record['record_date']);
                        if ($earliestDate === null || $recordDate < $earliestDate) {
                            $earliestDate = $recordDate;
                        }
                    }
                    
                    if ($earliestDate) {
                        // 计算从最早记录到今天的周数
                        $startWeek = clone $earliestDate;
                        $startWeek->modify('monday this week');
                        $endWeek = clone $today;
                        $endWeek->modify('monday this week');
                        $totalWeeks = (int)ceil(($endWeek->diff($startWeek)->days) / 7) + 1;
                        
                        // 按周分组统计完成情况
                        foreach ($allRecords as $record) {
                            if ($record['completed'] == 1) {
                                $recordDate = new \DateTime($record['record_date']);
                                $weekStart = clone $recordDate;
                                $weekStart->modify('monday this week');
                                $weekKey = $weekStart->format('Y-W');
                                if (!isset($weekMap[$weekKey])) {
                                    $weekMap[$weekKey] = true;
                                    $completedWeeks++;
                                }
                            }
                        }
                    }
                }
                
                $completionRate = $totalWeeks > 0 ? round(($completedWeeks / $totalWeeks) * 100) : 0;
                $currentStreak = $this->calculateCurrentWeekStreak($allRecords);
                $longestStreak = $this->calculateLongestWeekStreak($allRecords);
                
                return [
                    'target_type' => 'weekly',
                    'total_periods' => $totalWeeks,
                    'total_periods_label' => '总周数',
                    'completed_periods' => $completedWeeks,
                    'completed_periods_label' => '已完成周数',
                    'completion_rate' => $completionRate,
                    'completion_rate_desc' => '已完成数 / 总周数',
                    'current_streak' => $currentStreak,
                    'current_streak_label' => '连续周数',
                    'current_streak_desc' => '当前连续完成周数',
                    'longest_streak' => $longestStreak,
                    'longest_streak_label' => '最长连续',
                    'longest_streak_desc' => '历史最长连续周数',
                    'total_records' => $totalRecords,
                ];

            case 'monthly':
                // 每月完成：统计月数
                $totalMonths = 0;
                $completedMonths = 0;
                $monthMap = [];
                
                if (!empty($allRecords)) {
                    $earliestDate = null;
                    foreach ($allRecords as $record) {
                        $recordDate = new \DateTime($record['record_date']);
                        if ($earliestDate === null || $recordDate < $earliestDate) {
                            $earliestDate = $recordDate;
                        }
                    }
                    
                    if ($earliestDate) {
                        // 计算从最早记录到今天的月数
                        $startMonth = new \DateTime($earliestDate->format('Y-m-01'));
                        $endMonth = new \DateTime($today->format('Y-m-01'));
                        $diff = $endMonth->diff($startMonth);
                        $totalMonths = $diff->y * 12 + $diff->m + 1;
                        
                        // 按月分组统计完成情况
                        foreach ($allRecords as $record) {
                            if ($record['completed'] == 1) {
                                $recordDate = new \DateTime($record['record_date']);
                                $monthKey = $recordDate->format('Y-m');
                                if (!isset($monthMap[$monthKey])) {
                                    $monthMap[$monthKey] = true;
                                    $completedMonths++;
                                }
                            }
                        }
                    }
                }
                
                $completionRate = $totalMonths > 0 ? round(($completedMonths / $totalMonths) * 100) : 0;
                $currentStreak = $this->calculateCurrentMonthStreak($allRecords);
                $longestStreak = $this->calculateLongestMonthStreak($allRecords);
                
                return [
                    'target_type' => 'monthly',
                    'total_periods' => $totalMonths,
                    'total_periods_label' => '总月数',
                    'completed_periods' => $completedMonths,
                    'completed_periods_label' => '已完成月数',
                    'completion_rate' => $completionRate,
                    'completion_rate_desc' => '已完成数 / 总月数',
                    'current_streak' => $currentStreak,
                    'current_streak_label' => '连续月数',
                    'current_streak_desc' => '当前连续完成月数',
                    'longest_streak' => $longestStreak,
                    'longest_streak_label' => '最长连续',
                    'longest_streak_desc' => '历史最长连续月数',
                    'total_records' => $totalRecords,
                ];

            case 'yearly':
                // 每年完成：统计年数
                $totalYears = 0;
                $completedYears = 0;
                $yearMap = [];
                
                if (!empty($allRecords)) {
                    $earliestDate = null;
                    foreach ($allRecords as $record) {
                        $recordDate = new \DateTime($record['record_date']);
                        if ($earliestDate === null || $recordDate < $earliestDate) {
                            $earliestDate = $recordDate;
                        }
                    }
                    
                    if ($earliestDate) {
                        // 计算从最早记录到今天的年数
                        $startYear = (int)$earliestDate->format('Y');
                        $endYear = (int)$today->format('Y');
                        $totalYears = $endYear - $startYear + 1;
                        
                        // 按年分组统计完成情况
                        foreach ($allRecords as $record) {
                            if ($record['completed'] == 1) {
                                $recordDate = new \DateTime($record['record_date']);
                                $yearKey = $recordDate->format('Y');
                                if (!isset($yearMap[$yearKey])) {
                                    $yearMap[$yearKey] = true;
                                    $completedYears++;
                                }
                            }
                        }
                    }
                }
                
                $completionRate = $totalYears > 0 ? round(($completedYears / $totalYears) * 100) : 0;
                $currentStreak = $this->calculateCurrentYearStreak($allRecords);
                $longestStreak = $this->calculateLongestYearStreak($allRecords);
                
                return [
                    'target_type' => 'yearly',
                    'total_periods' => $totalYears,
                    'total_periods_label' => '总年数',
                    'completed_periods' => $completedYears,
                    'completed_periods_label' => '已完成年数',
                    'completion_rate' => $completionRate,
                    'completion_rate_desc' => '已完成数 / 总年数',
                    'current_streak' => $currentStreak,
                    'current_streak_label' => '连续年数',
                    'current_streak_desc' => '当前连续完成年数',
                    'longest_streak' => $longestStreak,
                    'longest_streak_label' => '最长连续',
                    'longest_streak_desc' => '历史最长连续年数',
                    'total_records' => $totalRecords,
                ];

            default:
                // 默认按每天处理
                return $this->calculateStatsByTargetType($habit, $allRecords, 'daily');
        }
    }

    /**
     * 计算当前连续周数
     */
    private function calculateCurrentWeekStreak($records)
    {
        $streak = 0;
        $today = new \DateTime();
        $currentWeek = clone $today;
        $currentWeek->modify('monday this week');
        
        while (true) {
            $weekStart = clone $currentWeek;
            $weekEnd = clone $currentWeek;
            $weekEnd->modify('+6 days');
            
            $weekKey = $weekStart->format('Y-W');
            $hasCompleted = false;
            
            foreach ($records as $record) {
                if ($record['completed'] == 1) {
                    $recordDate = new \DateTime($record['record_date']);
                    if ($recordDate >= $weekStart && $recordDate <= $weekEnd) {
                        $hasCompleted = true;
                        break;
                    }
                }
            }
            
            if ($hasCompleted) {
                $streak++;
                $currentWeek->modify('-1 week');
            } else {
                break;
            }
        }
        
        return $streak;
    }

    /**
     * 计算最长连续周数
     */
    private function calculateLongestWeekStreak($records)
    {
        // 收集所有“有完成记录”的周起始日期（周一），去重
        $weekStarts = [];
        foreach ($records as $record) {
            if ($record['completed'] == 1) {
                $recordDate = new \DateTime($record['record_date']);
                $weekStart = clone $recordDate;
                $weekStart->modify('monday this week');
                $key = $weekStart->format('Y-m-d');
                if (!in_array($key, $weekStarts, true)) {
                    $weekStarts[] = $key;
                }
            }
        }

        if (empty($weekStarts)) {
            return 0;
        }

        // 按时间排序
        sort($weekStarts);

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < count($weekStarts); $i++) {
            $prev = new \DateTime($weekStarts[$i - 1]);
            $curr = new \DateTime($weekStarts[$i]);
            $diffDays = (int)$curr->diff($prev)->days;

            // 相邻周起始日相差 7 天，说明是连续周
            if ($diffDays === 7) {
                $currentStreak++;
            } else {
                $longestStreak = max($longestStreak, $currentStreak);
                $currentStreak = 1;
            }
        }

        return max($longestStreak, $currentStreak);
    }

    /**
     * 计算当前连续月数
     */
    private function calculateCurrentMonthStreak($records)
    {
        $streak = 0;
        $today = new \DateTime();
        $currentMonth = new \DateTime($today->format('Y-m-01'));
        
        while (true) {
            $monthKey = $currentMonth->format('Y-m');
            $hasCompleted = false;
            
            foreach ($records as $record) {
                if ($record['completed'] == 1) {
                    $recordDate = new \DateTime($record['record_date']);
                    $recordMonth = $recordDate->format('Y-m');
                    if ($recordMonth === $monthKey) {
                        $hasCompleted = true;
                        break;
                    }
                }
            }
            
            if ($hasCompleted) {
                $streak++;
                $currentMonth->modify('-1 month');
            } else {
                break;
            }
        }
        
        return $streak;
    }

    /**
     * 计算最长连续月数
     */
    private function calculateLongestMonthStreak($records)
    {
        $completedMonths = [];
        foreach ($records as $record) {
            if ($record['completed'] == 1) {
                $recordDate = new \DateTime($record['record_date']);
                $monthKey = $recordDate->format('Y-m');
                if (!in_array($monthKey, $completedMonths)) {
                    $completedMonths[] = $monthKey;
                }
            }
        }
        
        if (empty($completedMonths)) {
            return 0;
        }
        
        sort($completedMonths);
        $longestStreak = 1;
        $currentStreak = 1;
        
        for ($i = 1; $i < count($completedMonths); $i++) {
            $prevMonth = new \DateTime($completedMonths[$i - 1] . '-01');
            $currMonth = new \DateTime($completedMonths[$i] . '-01');
            $diff = (int)$currMonth->diff($prevMonth)->m;
            
            if ($diff == 1) {
                $currentStreak++;
            } else {
                $longestStreak = max($longestStreak, $currentStreak);
                $currentStreak = 1;
            }
        }
        
        return max($longestStreak, $currentStreak);
    }

    /**
     * 计算当前连续年数
     */
    private function calculateCurrentYearStreak($records)
    {
        $streak = 0;
        $today = new \DateTime();
        $currentYear = (int)$today->format('Y');
        
        while (true) {
            $hasCompleted = false;
            
            foreach ($records as $record) {
                if ($record['completed'] == 1) {
                    $recordDate = new \DateTime($record['record_date']);
                    $recordYear = (int)$recordDate->format('Y');
                    if ($recordYear === $currentYear) {
                        $hasCompleted = true;
                        break;
                    }
                }
            }
            
            if ($hasCompleted) {
                $streak++;
                $currentYear--;
            } else {
                break;
            }
        }
        
        return $streak;
    }

    /**
     * 计算最长连续年数
     */
    private function calculateLongestYearStreak($records)
    {
        $completedYears = [];
        foreach ($records as $record) {
            if ($record['completed'] == 1) {
                $recordDate = new \DateTime($record['record_date']);
                $year = (int)$recordDate->format('Y');
                if (!in_array($year, $completedYears)) {
                    $completedYears[] = $year;
                }
            }
        }
        
        if (empty($completedYears)) {
            return 0;
        }
        
        sort($completedYears);
        $longestStreak = 1;
        $currentStreak = 1;
        
        for ($i = 1; $i < count($completedYears); $i++) {
            if ($completedYears[$i] - $completedYears[$i - 1] == 1) {
                $currentStreak++;
            } else {
                $longestStreak = max($longestStreak, $currentStreak);
                $currentStreak = 1;
            }
        }
        
        return max($longestStreak, $currentStreak);
    }

    /**
     * 根据目标类型生成不同的视图数据
     */
    private function generateViewDataByTargetType($targetType, $allRecords, $createdAt)
    {
        switch ($targetType) {
            case 'daily':
                return [
                    'view_type' => 'daily',
                    'heatmap' => $this->generateHeatmap($allRecords, $createdAt),
                    'trend' => $this->generateMonthlyTrend($allRecords),
                    'trend_label' => '月度趋势'
                ];

            case 'weekly':
                return [
                    'view_type' => 'weekly',
                    'heatmap' => $this->generateWeeklyHeatmap($allRecords, $createdAt),
                    'trend' => $this->generateWeeklyTrend($allRecords),
                    'trend_label' => '周度趋势'
                ];

            case 'monthly':
                return [
                    'view_type' => 'monthly',
                    'heatmap' => $this->generateMonthlyHeatmap($allRecords, $createdAt),
                    'trend' => $this->generateMonthlyTrend($allRecords),
                    'trend_label' => '月度趋势'
                ];

            case 'yearly':
                return [
                    'view_type' => 'yearly',
                    'heatmap' => $this->generateYearlyHeatmap($allRecords, $createdAt),
                    'trend' => $this->generateYearlyTrend($allRecords),
                    'trend_label' => '年度趋势'
                ];

            default:
                return [
                    'view_type' => 'daily',
                    'heatmap' => $this->generateHeatmap($allRecords, $createdAt),
                    'trend' => $this->generateMonthlyTrend($allRecords),
                    'trend_label' => '月度趋势'
                ];
        }
    }

    /**
     * 生成周度热力图数据
     */
    private function generateWeeklyHeatmap($records, $createdAt)
    {
        $heatmap = [];
        $today = new \DateTime();
        
        // 计算最近 12 周（不依赖创建时间），从本周往前推 11 周
        $currentWeek = clone $today;
        $currentWeek->modify('monday this week');
        $weeksToShow = 12;

        $recordsMap = [];
        foreach ($records as $record) {
            $recordDate = new \DateTime($record['record_date']);
            $weekStart = clone $recordDate;
            $weekStart->modify('monday this week');
            $weekKey = $weekStart->format('Y-W');
            if (!isset($recordsMap[$weekKey])) {
                $recordsMap[$weekKey] = ['completed' => 0, 'total' => 0];
            }
            $recordsMap[$weekKey]['total']++;
            if ($record['completed'] == 1) {
                $recordsMap[$weekKey]['completed']++;
            }
        }

        for ($i = $weeksToShow - 1; $i >= 0; $i--) {
            $weekStart = clone $currentWeek;
            $weekStart->modify("-{$i} week");
            $weekKey = $currentWeek->format('Y-W');
            $weekEnd = clone $weekStart;
            $weekEnd->modify('+6 days');
            
            $completed = isset($recordsMap[$weekKey]) && $recordsMap[$weekKey]['completed'] > 0 ? 1 : 0;
            $level = $completed ? 3 : 0;
            
            $heatmap[] = [
                'date' => $weekStart->format('Y-m-d'),
                'week_key' => $weekKey,
                'week_label' => $weekStart->format('Y年m月d日') . '-' . $weekEnd->format('m月d日'),
                'completed' => $completed,
                'level' => $level,
            ];
        }

        return $heatmap;
    }

    /**
     * 生成月度热力图数据
     */
    private function generateMonthlyHeatmap($records, $createdAt)
    {
        $heatmap = [];
        $today = new \DateTime();
        
        // 最近 12 个月，从当前月往前推 11 个月
        $currentMonth = new \DateTime($today->format('Y-m-01'));
        $monthsToShow = 12;

        $recordsMap = [];
        foreach ($records as $record) {
            $recordDate = new \DateTime($record['record_date']);
            $monthKey = $recordDate->format('Y-m');
            if (!isset($recordsMap[$monthKey])) {
                $recordsMap[$monthKey] = ['completed' => 0, 'total' => 0];
            }
            $recordsMap[$monthKey]['total']++;
            if ($record['completed'] == 1) {
                $recordsMap[$monthKey]['completed']++;
            }
        }

        for ($i = $monthsToShow - 1; $i >= 0; $i--) {
            $month = new \DateTime($currentMonth->format('Y-m-01'));
            if ($i > 0) {
                $month->modify("-{$i} month");
            }
            $monthKey = $month->format('Y-m');
            $completed = isset($recordsMap[$monthKey]) && $recordsMap[$monthKey]['completed'] > 0 ? 1 : 0;
            $level = $completed ? 3 : 0;
            
            $heatmap[] = [
                'date' => $month->format('Y-m-01'),
                'month_key' => $monthKey,
                'month_label' => $month->format('Y年m月'),
                'completed' => $completed,
                'level' => $level,
            ];
        }

        return $heatmap;
    }

    /**
     * 生成年度热力图数据
     */
    private function generateYearlyHeatmap($records, $createdAt)
    {
        $heatmap = [];
        $today = new \DateTime();
        $endYear = (int)$today->format('Y');
        $yearsToShow = 5;
        $startYear = $endYear - $yearsToShow + 1;

        // 按年分组
        $recordsMap = [];
        foreach ($records as $record) {
            $recordDate = new \DateTime($record['record_date']);
            $yearKey = $recordDate->format('Y');
            if (!isset($recordsMap[$yearKey])) {
                $recordsMap[$yearKey] = ['completed' => 0, 'total' => 0];
            }
            $recordsMap[$yearKey]['total']++;
            if ($record['completed'] == 1) {
                $recordsMap[$yearKey]['completed']++;
            }
        }

        for ($year = $startYear; $year <= $endYear; $year++) {
            $yearKey = (string)$year;
            $completed = isset($recordsMap[$yearKey]) && $recordsMap[$yearKey]['completed'] > 0 ? 1 : 0;
            $level = $completed ? 3 : 0;
            
            $heatmap[] = [
                'date' => $year . '-01-01',
                'year_key' => $yearKey,
                'year_label' => $year . '年',
                'completed' => $completed,
                'level' => $level,
            ];
        }

        return $heatmap;
    }

    /**
     * 生成周度趋势数据
     */
    private function generateWeeklyTrend($records)
    {
        $weeklyData = [];
        $recordsMap = [];

        foreach ($records as $record) {
            $recordDate = new \DateTime($record['record_date']);
            $weekStart = clone $recordDate;
            $weekStart->modify('monday this week');
            $weekKey = $weekStart->format('Y-W');
            
            if (!isset($recordsMap[$weekKey])) {
                $recordsMap[$weekKey] = ['completed' => 0, 'total' => 0, 'week_start' => $weekStart];
            }
            $recordsMap[$weekKey]['total']++;
            if ($record['completed'] == 1) {
                $recordsMap[$weekKey]['completed']++;
            }
        }

        foreach ($recordsMap as $weekKey => $data) {
            $weekEnd = clone $data['week_start'];
            $weekEnd->modify('+6 days');
            
            $weeklyData[] = [
                'period' => $weekKey,
                'period_label' => $data['week_start']->format('m/d') . '-' . $weekEnd->format('m/d'),
                'completed' => $data['completed'],
                'total' => $data['total'],
                'rate' => $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0,
            ];
        }

        usort($weeklyData, function($a, $b) {
            return strcmp($a['period'], $b['period']);
        });

        return $weeklyData;
    }

    /**
     * 生成年度趋势数据
     */
    private function generateYearlyTrend($records)
    {
        $yearlyData = [];
        $recordsMap = [];

        foreach ($records as $record) {
            $year = substr($record['record_date'], 0, 4); // YYYY
            if (!isset($recordsMap[$year])) {
                $recordsMap[$year] = ['completed' => 0, 'total' => 0];
            }
            $recordsMap[$year]['total']++;
            if ($record['completed'] == 1) {
                $recordsMap[$year]['completed']++;
            }
        }

        foreach ($recordsMap as $year => $data) {
            $yearlyData[] = [
                'period' => $year,
                'period_label' => $year . '年',
                'completed' => $data['completed'],
                'total' => $data['total'],
                'rate' => $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0,
            ];
        }

        usort($yearlyData, function($a, $b) {
            return strcmp($a['period'], $b['period']);
        });

        return $yearlyData;
    }
}
