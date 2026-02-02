<?php
namespace app\middleware;

use think\Request;

class Auth
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        // 从请求头获取 token
        $token = $request->header('Authorization');
        if ($token) {
            $token = str_replace('Bearer ', '', $token);
        } else {
            $token = $request->param('token');
        }

        if (!$token) {
            return json([
                'success' => false,
                'message' => '未登录，请先登录'
            ], 401);
        }

        // 验证 token
        $userId = $this->getUserIdFromToken($token);
        if (!$userId) {
            return json([
                'success' => false,
                'message' => 'token 无效或已过期'
            ], 401);
        }

        // 将用户ID存储到请求中，供控制器使用
        $request->userId = $userId;

        return $next($request);
    }

    /**
     * 从 token 获取用户ID
     */
    private function getUserIdFromToken($token)
    {
        try {
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);
            
            if (count($parts) !== 3) {
                return null;
            }

            $userId = $parts[0];
            $timestamp = $parts[1];
            $hash = $parts[2];

            // 验证 token
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
}
