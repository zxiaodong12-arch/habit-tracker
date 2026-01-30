<?php
namespace app\controller;

use app\BaseController;
use app\model\Habit as HabitModel;
use app\model\HabitRecord as HabitRecordModel;
use think\facade\Db;

class Stats extends BaseController
{
    /**
     * 获取习惯统计信息
     */
    public function getHabitStats($habitId)
    {
        try {
            // 检查习惯是否存在
            $habit = HabitModel::find($habitId);
            if (!$habit) {
                return json([
                    'success' => false,
                    'message' => '习惯不存在'
                ], 404);
            }

            // 获取所有记录
            $allRecords = HabitRecordModel::where('habit_id', $habitId)
                ->order('record_date', 'asc')
                ->select()
                ->toArray();

            $totalRecords = count($allRecords);
            $completedRecords = array_filter($allRecords, function($r) {
                return $r['completed'] == 1;
            });
            $completedCount = count($completedRecords);

            // 计算完成率
            $completionRate = 0;
            $totalDays = 0;
            $firstDate = null;

            if (count($completedRecords) > 0) {
                $firstDate = $completedRecords[0]['record_date'];
                $today = date('Y-m-d');
                $firstDateObj = new \DateTime($firstDate);
                $todayObj = new \DateTime($today);
                $totalDays = $todayObj->diff($firstDateObj)->days + 1;
                if ($totalDays > 0) {
                    $completionRate = round(($completedCount / $totalDays) * 100);
                }
            }

            // 计算连续天数
            $streak = 0;
            $today = date('Y-m-d');
            $todayRecord = array_filter($allRecords, function($r) use ($today) {
                return $r['record_date'] === $today && $r['completed'] == 1;
            });

            $checkDate = new \DateTime($today);
            if (empty($todayRecord)) {
                $checkDate->modify('-1 day');
            }

            while (true) {
                $dateStr = $checkDate->format('Y-m-d');
                $record = array_filter($allRecords, function($r) use ($dateStr) {
                    return $r['record_date'] === $dateStr && $r['completed'] == 1;
                });

                if (!empty($record)) {
                    $streak++;
                    $checkDate->modify('-1 day');
                } else {
                    break;
                }
            }

            // 计算最长连续天数
            $longestStreak = 0;
            $currentStreak = 0;
            $sortedRecords = array_values($completedRecords);
            usort($sortedRecords, function($a, $b) {
                return strcmp($a['record_date'], $b['record_date']);
            });

            for ($i = 0; $i < count($sortedRecords); $i++) {
                if ($i === 0) {
                    $currentStreak = 1;
                } else {
                    $prevDate = new \DateTime($sortedRecords[$i - 1]['record_date']);
                    $currDate = new \DateTime($sortedRecords[$i]['record_date']);
                    $diffDays = $currDate->diff($prevDate)->days;

                    if ($diffDays === 1) {
                        $currentStreak++;
                    } else {
                        $longestStreak = max($longestStreak, $currentStreak);
                        $currentStreak = 1;
                    }
                }
            }
            $longestStreak = max($longestStreak, $currentStreak);

            return json([
                'success' => true,
                'data' => [
                    'habit_id' => (int)$habitId,
                    'total_records' => $totalRecords,
                    'completed_count' => $completedCount,
                    'completion_rate' => $completionRate,
                    'total_days' => $totalDays,
                    'first_date' => $firstDate,
                    'current_streak' => $streak,
                    'longest_streak' => $longestStreak
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取统计信息失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 获取用户统计信息
     */
    public function getUserStats($userId)
    {
        try {
            // 获取所有未归档的习惯
            $habits = HabitModel::where([
                'user_id' => $userId,
                'archived' => 0
            ])->select();

            $totalHabits = count($habits);
            $todayCompleted = 0;
            $longestStreak = 0;
            $today = date('Y-m-d');

            foreach ($habits as $habit) {
                // 检查今天是否完成
                $todayRecord = HabitRecordModel::where([
                    'habit_id' => $habit->id,
                    'record_date' => $today,
                    'completed' => 1
                ])->find();

                if ($todayRecord) {
                    $todayCompleted++;
                }

                // 获取习惯的最长连续天数
                $records = HabitRecordModel::where([
                    'habit_id' => $habit->id,
                    'completed' => 1
                ])->order('record_date', 'asc')->select()->toArray();

                $currentStreak = 0;
                $habitLongestStreak = 0;

                for ($i = 0; $i < count($records); $i++) {
                    if ($i === 0) {
                        $currentStreak = 1;
                    } else {
                        $prevDate = new \DateTime($records[$i - 1]['record_date']);
                        $currDate = new \DateTime($records[$i]['record_date']);
                        $diffDays = $currDate->diff($prevDate)->days;

                        if ($diffDays === 1) {
                            $currentStreak++;
                        } else {
                            $habitLongestStreak = max($habitLongestStreak, $currentStreak);
                            $currentStreak = 1;
                        }
                    }
                }
                $habitLongestStreak = max($habitLongestStreak, $currentStreak);
                $longestStreak = max($longestStreak, $habitLongestStreak);
            }

            $todayCompletionRate = $totalHabits > 0 ? round(($todayCompleted / $totalHabits) * 100) : 0;

            return json([
                'success' => true,
                'data' => [
                    'user_id' => (int)$userId,
                    'total_habits' => $totalHabits,
                    'today_completed' => $todayCompleted,
                    'today_completion_rate' => $todayCompletionRate,
                    'longest_streak' => $longestStreak
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户统计失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
