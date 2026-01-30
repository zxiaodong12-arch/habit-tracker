<?php
namespace app\controller;

use app\BaseController;
use app\model\HabitRecord as HabitRecordModel;
use think\facade\Db;
use think\Request;

class Records extends BaseController
{
    /**
     * 获取某个习惯的打卡记录
     */
    public function getByHabit($habitId, Request $request)
    {
        try {
            $startDate = $request->param('start_date');
            $endDate = $request->param('end_date');
            $completed = $request->param('completed');

            $where = ['habit_id' => $habitId];

            if ($startDate) {
                $where[] = ['record_date', '>=', $startDate];
            }
            if ($endDate) {
                $where[] = ['record_date', '<=', $endDate];
            }
            if ($completed !== null) {
                $where['completed'] = $completed === 'true' ? 1 : 0;
            }

            $records = HabitRecordModel::where($where)
                ->order('record_date', 'desc')
                ->select();

            return json([
                'success' => true,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取打卡记录失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 获取单条打卡记录
     */
    public function read($id)
    {
        try {
            $record = HabitRecordModel::find($id);
            
            if (!$record) {
                return json([
                    'success' => false,
                    'message' => '记录不存在'
                ], 404);
            }

            return json([
                'success' => true,
                'data' => $record
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取打卡记录失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 创建或更新打卡记录
     */
    public function save(Request $request)
    {
        try {
            $data = $request->post();

            if (empty($data['habit_id']) || empty($data['record_date'])) {
                return json([
                    'success' => false,
                    'message' => 'habit_id 和 record_date 必填'
                ], 400);
            }

            // 检查是否已存在
            $existing = HabitRecordModel::where([
                'habit_id' => $data['habit_id'],
                'record_date' => $data['record_date']
            ])->find();

            if ($existing) {
                // 更新现有记录
                $existing->completed = isset($data['completed']) ? ($data['completed'] ? 1 : 0) : 1;
                $existing->save();
                $record = $existing;
            } else {
                // 创建新记录
                $record = new HabitRecordModel();
                $record->habit_id = $data['habit_id'];
                $record->record_date = $data['record_date'];
                $record->completed = isset($data['completed']) ? ($data['completed'] ? 1 : 0) : 1;
                $record->save();
            }

            return json([
                'success' => true,
                'data' => $record
            ], 201);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '操作失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 切换打卡状态
     */
    public function toggle(Request $request)
    {
        try {
            $data = $request->post();

            if (empty($data['habit_id']) || empty($data['record_date'])) {
                return json([
                    'success' => false,
                    'message' => 'habit_id 和 record_date 必填'
                ], 400);
            }

            // 查找现有记录
            $record = HabitRecordModel::where([
                'habit_id' => $data['habit_id'],
                'record_date' => $data['record_date']
            ])->find();

            if ($record) {
                // 切换状态
                $record->completed = $record->completed == 1 ? 0 : 1;
                $record->save();
            } else {
                // 创建新记录（默认为完成）
                $record = new HabitRecordModel();
                $record->habit_id = $data['habit_id'];
                $record->record_date = $data['record_date'];
                $record->completed = 1;
                $record->save();
            }

            return json([
                'success' => true,
                'data' => $record
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
     * 删除打卡记录
     */
    public function delete($id)
    {
        try {
            $record = HabitRecordModel::find($id);
            
            if (!$record) {
                return json([
                    'success' => false,
                    'message' => '记录不存在'
                ], 404);
            }

            $record->delete();

            return json([
                'success' => true,
                'message' => '记录删除成功'
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '删除失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
