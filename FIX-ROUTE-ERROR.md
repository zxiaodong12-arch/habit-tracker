# ThinkPHP 路由错误修复指南

## 错误说明

错误：`控制器不存在:app\controller\Index`

**原因：** ThinkPHP 无法找到正确的路由，试图访问默认的 `Index` 控制器。

## 问题分析

访问 `http://1.15.12.78/api/health` 时，ThinkPHP 应该：
1. 匹配到路由 `/api/health`
2. 执行对应的控制器或闭包函数

但实际却试图访问 `app\controller\Index`，说明路由没有正确匹配。

## 解决方案

### 方案一：检查路由文件是否正确加载

ThinkPHP 6.x 使用 `route/app.php` 作为路由文件，而不是 `config/route.php`。

#### 1. 检查路由文件

```bash
# 在服务器上检查
ls -la /var/www/habit-tracker/api/route/app.php
ls -la /var/www/habit-tracker/api/config/route.php
```

#### 2. 确保路由文件内容正确

路由文件应该在 `route/app.php`，内容应该是：

```php
<?php
use think\facade\Route;

// API 路由组
Route::group('api', function () {
    // 健康检查
    Route::get('health', 'Health/index');
    // 或使用闭包
    Route::get('health', function () {
        return json(['status' => 'ok', 'timestamp' => date('c')]);
    });

    // 认证相关
    Route::group('auth', function () {
        Route::post('register', 'Auth/register');
        Route::post('login', 'Auth/login');
        Route::post('logout', 'Auth/logout');
        Route::get('me', 'Auth/me');
    });

    // 其他路由...
})->allowCrossDomain();
```

#### 3. 检查应用配置

确保 `config/app.php` 中启用了路由：

```php
'with_route' => true,
```

### 方案二：修复 Nginx 重写规则

确保 Nginx 配置正确重写 URL：

```nginx
location / {
    try_files $uri $uri/ /index.php?s=$uri&$args;
}
```

或使用更明确的 ThinkPHP 重写规则：

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

### 方案三：创建 Index 控制器（临时方案）

如果路由仍然不工作，可以创建一个简单的 Index 控制器：

```bash
# 在服务器上创建
cat > /var/www/habit-tracker/api/app/controller/Index.php << 'EOF'
<?php
namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return json(['message' => 'API is running', 'version' => '1.0']);
    }
}
EOF
```

### 方案四：检查访问路径

确保访问的是正确的路径：

- ✅ 正确：`http://1.15.12.78/api/health`
- ❌ 错误：`http://1.15.12.78/health`
- ❌ 错误：`http://1.15.12.78/index.php/api/health`

## 快速修复步骤

### 1. 检查路由文件

```bash
# 在服务器上执行
cd /var/www/habit-tracker/api

# 检查路由文件是否存在
ls -la route/app.php

# 查看路由文件内容
cat route/app.php | head -30
```

### 2. 确保路由文件正确

如果 `route/app.php` 不存在或内容不对，创建/修复它：

```bash
cat > route/app.php << 'EOF'
<?php
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

    // 认证相关
    Route::group('auth', function () {
        Route::post('register', 'Auth/register');
        Route::post('login', 'Auth/login');
        Route::post('logout', 'Auth/logout');
        Route::get('me', 'Auth/me');
    });

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
        Route::post('toggle', 'Records/toggle');
    });

    // 统计信息
    Route::group('stats', function () {
        Route::get('habit/:habitId', 'Stats/getHabitStats');
        Route::get('user/:userId', 'Stats/getUserStats');
    });
})->allowCrossDomain();
EOF
```

### 3. 检查 Nginx 配置

```bash
# 查看 Nginx 配置
cat /www/server/nginx/conf/vhost/habit-tracker-api.conf | grep -A 5 "location /"
```

确保有正确的重写规则。

### 4. 清除缓存

```bash
# 清除 ThinkPHP 路由缓存
rm -rf /var/www/habit-tracker/api/runtime/cache/*
rm -rf /var/www/habit-tracker/api/runtime/temp/*

# 确保 runtime 目录权限
chmod -R 777 /var/www/habit-tracker/api/runtime
```

### 5. 测试

```bash
# 测试路由
curl http://1.15.12.78/api/health

# 应该返回 JSON 响应
```

## 验证步骤

### 1. 检查路由是否加载

在服务器上创建一个测试文件：

```bash
cat > /var/www/habit-tracker/api/public/test-route.php << 'EOF'
<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new think\App();
$app->initialize();

// 获取所有路由
$routes = \think\facade\Route::getRuleList();
print_r($routes);
EOF
```

访问：`http://1.15.12.78/test-route.php` 查看路由列表。

### 2. 检查日志

```bash
# 查看 ThinkPHP 日志
tail -50 /var/www/habit-tracker/api/runtime/log/$(date +%Y%m)/*.log

# 查看 Nginx 错误日志
tail -50 /www/server/nginx/logs/error.log
```

## 常见问题

### Q: 路由文件在哪个位置？

**A:** ThinkPHP 6.x 使用 `route/app.php`，不是 `config/route.php`。

### Q: 如何确认路由已加载？

**A:** 
1. 检查 `config/app.php` 中 `with_route => true`
2. 查看日志文件
3. 使用测试文件输出路由列表

### Q: 访问路径应该是怎样的？

**A:** 
- 基础路径：`http://你的IP/api/`
- 健康检查：`http://你的IP/api/health`
- 注册：`http://你的IP/api/auth/register`

### Q: 为什么还是访问 Index 控制器？

**A:** 
1. 路由文件未正确加载
2. Nginx 重写规则不正确
3. 访问路径不对（缺少 `/api` 前缀）

## 完整检查清单

- [ ] `route/app.php` 文件存在且内容正确
- [ ] `config/app.php` 中 `with_route => true`
- [ ] Nginx 配置中有正确的 URL 重写规则
- [ ] 访问路径包含 `/api` 前缀
- [ ] runtime 目录权限正确（777）
- [ ] 已清除路由缓存
- [ ] 控制器文件存在且命名空间正确
