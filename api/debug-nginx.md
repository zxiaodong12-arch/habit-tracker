# Nginx "File not found" 错误排查

## 问题现象

访问 `http://habit-tracker.com/api/health` 返回 "File not found"

## 排查步骤

### 1. 检查 Nginx 配置是否正确加载

```bash
# 测试配置语法
nginx -t

# 查看实际加载的配置
nginx -T | grep -A 5 "server_name"
```

### 2. 检查 root 路径是否正确

```bash
# 确认 public 目录存在
ls -la /System/Volumes/Data/data/RD/habit-tracker/api/public

# 确认 index.php 存在
ls -la /System/Volumes/Data/data/RD/habit-tracker/api/public/index.php
```

### 3. 检查 PHP-FPM 是否运行

```bash
# 检查 PHP-FPM 进程
ps aux | grep php-fpm

# 检查端口 9000 是否监听
lsof -i :9000

# 如果没有运行，启动 PHP-FPM
brew services start php
```

### 4. 检查 Nginx 错误日志

```bash
# 查看错误日志（根据你的配置路径）
tail -f /opt/homebrew/var/log/nginx/error.log
# 或
tail -f /usr/local/var/log/nginx/error.log

# 查看访问日志
tail -f /opt/homebrew/var/log/nginx/access.log
```

### 5. 测试 PHP 是否正常工作

创建测试文件 `/System/Volumes/Data/data/RD/habit-tracker/api/public/test.php`:

```php
<?php
phpinfo();
```

访问 `http://habit-tracker.com/test.php`，如果能看到 PHP 信息，说明 PHP-FPM 正常。

### 6. 检查 ThinkPHP 路由

```bash
# 直接访问 index.php
curl http://habit-tracker.com/index.php

# 应该返回 ThinkPHP 的响应或错误信息
```

### 7. 检查文件权限

```bash
# 确保 Nginx 可以读取文件
ls -la /System/Volumes/Data/data/RD/habit-tracker/api/public/

# 如果权限不足，修改权限
chmod 755 /System/Volumes/Data/data/RD/habit-tracker/api/public
chmod 644 /System/Volumes/Data/data/RD/habit-tracker/api/public/index.php
```

### 8. 检查 hosts 文件（如果使用自定义域名）

```bash
# 编辑 hosts 文件
sudo nano /etc/hosts

# 添加：
127.0.0.1 habit-tracker.com
```

## 常见问题解决

### 问题1: root 路径错误

**症状：** 日志显示找不到文件

**解决：** 确认 `root` 路径是绝对路径，指向 `public` 目录

```nginx
root /System/Volumes/Data/data/RD/habit-tracker/api/public;
```

### 问题2: PHP-FPM 未运行

**症状：** 502 Bad Gateway 或连接超时

**解决：**
```bash
brew services start php
# 或
php-fpm
```

### 问题3: URL 重写未生效

**症状：** 直接访问 `/api/health` 返回 404

**解决：** 确保 location / 块中有重写规则：

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```

### 问题4: ThinkPHP 路由未加载

**症状：** 访问返回 ThinkPHP 默认页面或路由错误

**解决：**
- 检查 `config/route.php` 是否存在
- 检查 `composer install` 是否执行
- 检查 `vendor` 目录是否存在

## 快速测试命令

```bash
# 1. 测试配置
nginx -t

# 2. 重启 Nginx
brew services restart nginx

# 3. 测试 PHP
curl http://habit-tracker.com/test.php

# 4. 测试 API
curl http://habit-tracker.com/index.php/api/health

# 5. 测试路由
curl http://habit-tracker.com/api/health
```

## 调试模式

在 `config/app.php` 中启用调试：

```php
'app_debug' => true,
```

这样可以看到详细的错误信息。
