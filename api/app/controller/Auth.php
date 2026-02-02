<?php
namespace app\controller;

use app\BaseController;
use app\model\User as UserModel;
use think\facade\Db;
use think\Request;

class Auth extends BaseController
{
    /**
     * 用户注册
     */
    public function register(Request $request)
    {
        try {
            $data = $request->post();

            // 验证必填字段
            if (empty($data['username']) || empty($data['password'])) {
                return json([
                    'success' => false,
                    'message' => '用户名和密码不能为空'
                ], 400);
            }

            // 检查用户名是否已存在
            $existing = UserModel::where('username', $data['username'])->find();
            if ($existing) {
                return json([
                    'success' => false,
                    'message' => '用户名已存在'
                ], 400);
            }

            // 检查邮箱是否已存在（如果提供了邮箱）
            if (!empty($data['email'])) {
                $existingEmail = UserModel::where('email', $data['email'])->find();
                if ($existingEmail) {
                    return json([
                        'success' => false,
                        'message' => '邮箱已被注册'
                    ], 400);
                }
            }

            // 创建用户
            $user = new UserModel();
            $user->username = $data['username'];
            $user->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $user->email = $data['email'] ?? null;
            $user->save();

            // 返回用户信息（不包含密码）
            return json([
                'success' => true,
                'message' => '注册成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ], 201);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '注册失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 用户登录
     */
    public function login(Request $request)
    {
        try {
            $data = $request->post();

            // 验证必填字段
            if (empty($data['username']) || empty($data['password'])) {
                return json([
                    'success' => false,
                    'message' => '用户名和密码不能为空'
                ], 400);
            }

            // 查找用户
            $user = UserModel::where('username', $data['username'])->find();
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户名或密码错误'
                ], 401);
            }

            // 验证密码
            if (!password_verify($data['password'], $user->password_hash)) {
                return json([
                    'success' => false,
                    'message' => '用户名或密码错误'
                ], 401);
            }

            // 生成简单的 token（生产环境建议使用 JWT）
            $token = $this->generateToken($user->id);

            // 返回用户信息和 token
            return json([
                'success' => true,
                'message' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '登录失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 获取当前用户信息
     */
    public function me(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            if (!$userId) {
                return json([
                    'success' => false,
                    'message' => '未登录或 token 无效'
                ], 401);
            }

            $user = UserModel::find($userId);
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户不存在'
                ], 404);
            }

            return json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户信息失败',
                'error' => config('app.app_debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 生成 token（简单实现，生产环境建议使用 JWT）
     */
    private function generateToken($userId)
    {
        // 简单的 token：base64(userId:timestamp:hash)
        $timestamp = time();
        $hash = md5($userId . $timestamp . 'habit-tracker-secret-key');
        $token = base64_encode($userId . ':' . $timestamp . ':' . $hash);
        
        // 将 token 存储到数据库或缓存（这里简化处理）
        // 生产环境建议使用 Redis 或数据库存储 token
        
        return $token;
    }

    /**
     * 从 token 获取用户ID
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
     * 登出
     */
    public function logout(Request $request)
    {
        // 简单实现，生产环境需要清除 token
        return json([
            'success' => true,
            'message' => '登出成功'
        ]);
    }
}
