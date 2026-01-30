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
            $userId = $request->userId ?? $request->param('user_id');
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
                'message' => '获取习惯详情失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
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
            $userId = $request->userId ?? $data['user_id'] ?? null;

            $habit = new HabitModel();
            $habit->user_id = $userId;
            $habit->name = $data['name'];
            $habit->emoji = $data['emoji'] ?? '📝';
            $habit->color = $data['color'] ?? '#10b981';
            $habit->archived = isset($data['archived']) && $data['archived'] ? 1 : 0;
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
}
