<?php
namespace app\controller;

use app\BaseController;
use think\Response;
use think\facade\Db;

class Health extends BaseController
{
    public function index()
    {
        try {
            // 检查数据库连接
            Db::query('SELECT 1');
            $dbStatus = 'healthy';
        } catch (\Exception $e) {
            $dbStatus = 'unhealthy: ' . $e->getMessage();
        }

        $data = [
            'status' => 'up',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $dbStatus,
            'server' => [
                'php_version' => PHP_VERSION,
                'framework' => 'ThinkPHP ' . app()->version(),
                'environment' => app()->env->get('APP_ENV', 'unknown'),
            ]
        ];

        return json($data);
    }
}