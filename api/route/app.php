<?php
// 路由配置文件
use think\facade\Route;

// API 路由组
Route::group('api', function () {

    // 健康检查路由
    Route::get('api/health', 'Health/index');

    // 习惯管理
    Route::group('habits', function () {
        Route::get('', 'Habits/index');
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
