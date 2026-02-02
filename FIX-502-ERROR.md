# 502 Bad Gateway 错误修复指南

## 错误说明

502 Bad Gateway 表示 Nginx 无法连接到 PHP-FPM 后端服务。

## 排查步骤

### 1. 检查 PHP-FPM 是否运行

```bash
# 检查 PHP-FPM 状态
systemctl status php-fpm
# 或
systemctl status php8.0-fpm
# 或（宝塔面板）
ps aux | grep php-fpm

# 如果没有运行，启动它
systemctl start php-fpm
systemctl enable php-fpm
```

### 2. 查找 PHP-FPM Socket 路径

```bash
# 方法 1: 查找 socket 文件
find /tmp -name "php*.sock" 2>/dev/null
find /var/run -name "php*.sock" 2>/dev/null
ls -la /tmp/php-cgi*.sock

# 方法 2: 查看 PHP-FPM 配置
find /etc -name "php-fpm*.conf" 2>/dev/null
find /www -name "php-fpm*.conf" 2>/dev/null

# 方法 3: 查看 PHP-FPM 进程
ps aux | grep php-fpm | grep -v grep
```

### 3. 检查 Nginx 配置中的 fastcgi_pass

```bash
# 查看你的 Nginx 配置
cat /www/server/nginx/conf/vhost/habit-tracker-api.conf | grep fastcgi_pass
```

确保 `fastcgi_pass` 指向正确的 socket 或端口。

### 4. 测试 PHP-FPM 连接

```bash
# 如果使用 Unix Socket
ls -la /tmp/php-cgi-74.sock  # 根据实际情况修改路径

# 如果使用 TCP
netstat -tlnp | grep 9000
```

## 解决方案

### 方案一：修复 fastcgi_pass 配置

根据找到的 PHP-FPM socket 路径，更新 Nginx 配置：

```bash
# 编辑配置文件
vi /www/server/nginx/conf/vhost/habit-tracker-api.conf
```

修改 `location ~ \.php$` 块中的 `fastcgi_pass`：

**如果找到 socket 文件（如 `/tmp/php-cgi-74.sock`）：**
```nginx
location ~ \.php$ {
    fastcgi_pass unix:/tmp/php-cgi-74.sock;  # 使用实际找到的路径
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

**如果使用 TCP（端口 9000）：**
```nginx
location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### 方案二：启动 PHP-FPM（如果没有运行）

```bash
# 查找 PHP-FPM 服务名
systemctl list-units | grep php

# 启动 PHP-FPM
systemctl start php-fpm
# 或
systemctl start php8.0-fpm
# 或（宝塔面板）
/etc/init.d/php-fpm-74 start  # 根据版本调整

# 设置开机自启
systemctl enable php-fpm
```

### 方案三：检查文件权限

```bash
# 检查项目目录权限
ls -la /var/www/habit-tracker/api/public

# 确保 Nginx 用户可以读取文件
chown -R nginx:nginx /var/www/habit-tracker/api
# 或（如果使用 www-data）
chown -R www-data:www-data /var/www/habit-tracker/api

# 设置目录权限
chmod -R 755 /var/www/habit-tracker/api
chmod -R 777 /var/www/habit-tracker/api/runtime
```

### 方案四：检查 PHP-FPM 配置

```bash
# 查找 PHP-FPM 配置文件
find /etc -name "www.conf" 2>/dev/null
find /www -name "www.conf" 2>/dev/null

# 查看 listen 配置
grep "listen" /etc/php-fpm.d/www.conf
# 或
grep "listen" /www/server/php/74/etc/php-fpm.d/www.conf  # 宝塔面板路径
```

确保 PHP-FPM 的 `listen` 配置与 Nginx 的 `fastcgi_pass` 匹配。

## 快速修复脚本

```bash
#!/bin/bash

echo "🔍 检查 PHP-FPM 状态..."

# 检查 PHP-FPM 是否运行
if systemctl is-active --quiet php-fpm || systemctl is-active --quiet php8.0-fpm; then
    echo "✅ PHP-FPM 正在运行"
else
    echo "❌ PHP-FPM 未运行，尝试启动..."
    systemctl start php-fpm 2>/dev/null || systemctl start php8.0-fpm 2>/dev/null
fi

# 查找 socket
echo ""
echo "🔍 查找 PHP-FPM Socket..."
SOCKET=$(find /tmp /var/run -name "php*.sock" 2>/dev/null | head -1)

if [ -n "$SOCKET" ]; then
    echo "✅ 找到 Socket: $SOCKET"
    echo ""
    echo "请更新 Nginx 配置中的 fastcgi_pass 为:"
    echo "fastcgi_pass unix:$SOCKET;"
else
    echo "⚠️  未找到 Socket，检查 TCP 端口..."
    if netstat -tlnp | grep -q ":9000"; then
        echo "✅ 找到 PHP-FPM 在端口 9000"
        echo "请使用: fastcgi_pass 127.0.0.1:9000;"
    else
        echo "❌ 未找到 PHP-FPM，请检查安装"
    fi
fi

# 检查 Nginx 配置
echo ""
echo "🔍 检查 Nginx 配置..."
if nginx -t 2>&1 | grep -q "successful"; then
    echo "✅ Nginx 配置正确"
    echo "🔄 重载 Nginx..."
    nginx -s reload
else
    echo "❌ Nginx 配置有误，请先修复"
    nginx -t
fi
```

## 常见问题

### Q: 如何确定 PHP 版本？

```bash
php -v
```

### Q: 宝塔面板如何查看 PHP-FPM？

1. 登录宝塔面板
2. 软件商店 → 已安装 → PHP
3. 点击设置 → 服务
4. 查看运行状态和 socket 路径

### Q: 如何查看错误日志？

```bash
# Nginx 错误日志
tail -f /www/server/nginx/logs/error.log

# PHP-FPM 错误日志
tail -f /www/server/php/74/var/log/php-fpm.log  # 根据版本调整
# 或
tail -f /var/log/php-fpm/error.log
```

### Q: 权限问题？

```bash
# 查看当前用户
whoami

# 查看 Nginx 运行用户
ps aux | grep nginx | grep -v grep

# 修改文件所有者
chown -R nginx:nginx /var/www/habit-tracker/api
```

## 验证修复

修复后，测试：

```bash
# 1. 测试配置
nginx -t

# 2. 重载 Nginx
nginx -s reload

# 3. 测试 API
curl http://1.15.12.78/api/health

# 4. 查看日志
tail -f /www/server/nginx/logs/error.log
```
