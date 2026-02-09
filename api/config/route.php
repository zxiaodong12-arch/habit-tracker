<?php
// 路由配置文件
use think\facade\Route;

// API 路由组
Route::group('api', function () {
    // 健康检查
    Route::get('health', function () {
        try {
            \think\facade\Db::execute('SELECT 1');
            $dbStatus = true;
        } catch (Exception $e) {
            $dbStatus = false;
        }
        
        return json([
            'status' => 'ok',
            'database' => $dbStatus ? 'connected' : 'disconnected',
            'timestamp' => date('c')
        ]);
    });

    // 认证相关（不需要 token）
    Route::group('auth', function () {
        Route::post('register', 'Auth/register');
        Route::post('login', 'Auth/login');
        Route::post('logout', 'Auth/logout');
        Route::get('me', 'Auth/me');
    });

    // 习惯管理
    Route::group('habits', function () {
        Route::get('', 'Habits/index');
        // 更具体的路由要放在前面，避免被 :id 匹配
        Route::get(':id/detail', 'Habits/detail');
        Route::get(':id', 'Habits/read');
        Route::post('', 'Habits/save');
        Route::put(':id', 'Habits/update');
        Route::delete(':id', 'Habits/delete');
        Route::patch(':id/archive', 'Habits/archive');
    });

    // 打卡记录
    Route::group('records', function () {
        Route::get('habit/:habitId', 'Records/getByHabit');
        Route::get(':id', 'Records/read');
        Route::post('', 'Records/save');
        Route::post('toggle', 'Records/toggle');
        Route::delete(':id', 'Records/delete');
    });

    // 统计信息
    Route::group('stats', function () {
        Route::get('habit/:habitId', 'Stats/getHabitStats');
        Route::get('user/:userId', 'Stats/getUserStats');
    });
})->allowCrossDomain();
