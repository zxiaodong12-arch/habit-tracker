# 调试步骤

## 当前问题

即使简单的 PHP 文件也返回 "File not found"，说明：
1. Nginx 可能使用了错误的 server 块
2. 或者 PHP-FPM 配置有问题

## 立即执行的调试步骤

### 1. 检查实际使用的 server 块

```bash
# 查看 Nginx 实际加载的配置
nginx -T 2>&1 | grep -B 5 -A 10 "habit-tracker.com"
```

### 2. 检查 PHP-FPM 是否能访问文件

```bash
# 测试 PHP-FPM 直接执行
php-fpm -t

# 或者直接测试 PHP
php /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php
```

### 3. 检查文件权限

```bash
ls -la /System/Volumes/Data/data/RD/habit-tracker/api/public/
```

### 4. 临时使用 8080 端口（绕过冲突）

修改 `/usr/local/etc/nginx/vhosts/habit-tracker.conf`：
```nginx
listen 8080;
```

然后：
```bash
brew services restart nginx
curl http://habit-tracker.com:8080/test.php
```

### 5. 检查 PHP-FPM 用户权限

```bash
# 查看 PHP-FPM 运行用户
ps aux | grep php-fpm | head -1

# 检查该用户是否能读取文件
sudo -u _www ls /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php
# 或
sudo -u nobody ls /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php
```

## 最可能的原因

**Nginx 使用了 `websocket.conf`（default_server）而不是 `habit-tracker.conf`**

### 解决方案A：临时禁用 websocket.conf

```bash
sudo mv /usr/local/etc/nginx/vhosts/websocket.conf /usr/local/etc/nginx/vhosts/websocket.conf.bak
brew services restart nginx
curl http://habit-tracker.com/test.php
```

### 解决方案B：使用不同端口

```bash
# 修改 habit-tracker.conf
sed -i '' 's/listen 80;/listen 8080;/' /usr/local/etc/nginx/vhosts/habit-tracker.conf
brew services restart nginx
curl http://habit-tracker.com:8080/test.php
```

## 验证步骤

执行后，应该能看到：
- `test.php` 返回 "PHP works at: ..."
- `index.php` 返回 ThinkPHP 响应
- `/api/health` 返回 JSON
