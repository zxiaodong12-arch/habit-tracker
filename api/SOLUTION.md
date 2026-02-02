# 问题解决方案

## 🔍 问题根源

发现**多个 Nginx 配置文件都在监听 80 端口**，其中 `websocket.conf` 标记为 `default_server`，会优先匹配所有请求，导致 `habit-tracker.com` 的请求被其他配置处理。

## ✅ 解决方案

### 方案1：使用不同的端口（最简单）

修改配置使用 8080 端口：

```nginx
server {
    listen 8080;
    server_name habit-tracker.com localhost;
    # ... 其他配置
}
```

然后访问：`http://habit-tracker.com:8080/api/health`

### 方案2：确保 server_name 正确匹配（推荐）

1. **确认 hosts 文件配置**
   ```bash
   cat /etc/hosts | grep habit-tracker
   # 应该显示：127.0.0.1 habit-tracker.com
   ```

2. **重启 Nginx**
   ```bash
   brew services restart nginx
   ```

3. **测试**
   ```bash
   curl http://habit-tracker.com/api/health
   ```

### 方案3：使用 default_server（如果端口 80 必须）

修改配置：
```nginx
server {
    listen 80 default_server;
    server_name habit-tracker.com;
    # ...
}
```

**注意：** 这会影响其他使用 80 端口的服务。

## 🚀 立即执行

### 步骤1：更新配置文件（已使用 try_files）

配置文件已更新为使用 `try_files`，更可靠。

### 步骤2：重启 Nginx

```bash
brew services restart nginx
```

### 步骤3：测试

```bash
# 测试直接访问 index.php
curl http://habit-tracker.com/index.php

# 测试路由
curl http://habit-tracker.com/api/health

# 测试带参数
curl "http://habit-tracker.com/index.php?s=/api/health"
```

## 📝 如果使用 8080 端口

如果选择方案1，修改配置后：

1. 更新配置文件中的端口
2. 重启 Nginx
3. 访问：`http://habit-tracker.com:8080/api/health`

## 🔧 调试命令

```bash
# 查看哪个 server 块处理了请求
tail -f /usr/local/var/log/nginx/habit-tracker-access.log

# 查看错误
tail -f /usr/local/var/log/nginx/habit-tracker-error.log

# 测试 PHP
echo "<?php phpinfo(); ?>" > /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php
curl http://habit-tracker.com/test.php
```
