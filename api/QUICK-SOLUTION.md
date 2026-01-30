# 快速解决方案

## 🎯 问题确认

`websocket.conf` 有 `default_server`，但 Nginx 应该根据 `server_name` 正确匹配 `habit-tracker.com`。

## ✅ 立即执行这3步

### 1. 确认 hosts 文件

```bash
# 检查 hosts 文件
cat /etc/hosts | grep habit-tracker

# 如果没有，添加：
sudo sh -c 'echo "127.0.0.1 habit-tracker.com" >> /etc/hosts'
```

### 2. 重启 Nginx

```bash
brew services restart nginx
```

### 3. 测试

```bash
# 测试1：直接访问 index.php
curl http://habit-tracker.com/index.php

# 测试2：测试路由
curl http://habit-tracker.com/api/health

# 测试3：带参数访问
curl "http://habit-tracker.com/index.php?s=/api/health"
```

## 🔍 如果还是不行

### 方案A：使用 8080 端口（最简单）

修改 `/usr/local/etc/nginx/vhosts/habit-tracker.conf`：
```nginx
listen 8080;
```

然后访问：`http://habit-tracker.com:8080/api/health`

### 方案B：检查实际匹配的 server

```bash
# 查看访问日志，看请求被哪个 server 处理
tail -f /usr/local/var/log/nginx/access.log
```

### 方案C：临时禁用 websocket.conf

```bash
# 重命名 websocket.conf（临时禁用）
sudo mv /usr/local/etc/nginx/vhosts/websocket.conf /usr/local/etc/nginx/vhosts/websocket.conf.bak
sudo nginx -s reload
```

## 📋 当前配置状态

- ✅ 配置文件：`/usr/local/etc/nginx/vhosts/habit-tracker.conf`
- ✅ 使用 `try_files`（更可靠）
- ✅ root 路径正确
- ✅ PHP-FPM 运行正常

**现在只需要重启 Nginx 并测试！**
