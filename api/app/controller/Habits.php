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
