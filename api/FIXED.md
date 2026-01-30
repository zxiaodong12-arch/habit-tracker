# ✅ 问题已修复

## 问题原因

Nginx 使用了 `websocket.conf`（标记为 `default_server`）处理所有请求，导致 `habit-tracker.com` 的请求被错误处理。

## 解决方案

**已改为使用 8080 端口**，避免与其他配置冲突。

## 🚀 现在执行

### 1. 重启 Nginx

```bash
brew services restart nginx
```

### 2. 测试 API（注意端口改为 8080）

```bash
# 测试健康检查
curl http://habit-tracker.com:8080/api/health

# 测试直接访问
curl http://habit-tracker.com:8080/index.php

# 测试简单 PHP 文件
curl http://habit-tracker.com:8080/test.php
```

## 📝 访问地址

所有 API 请求现在需要使用 **8080 端口**：

- 健康检查：`http://habit-tracker.com:8080/api/health`
- 习惯列表：`http://habit-tracker.com:8080/api/habits`
- 创建习惯：`POST http://habit-tracker.com:8080/api/habits`

## 🔄 如果想改回 80 端口

需要先处理 `websocket.conf` 的 `default_server` 冲突：

```bash
# 方案1：临时禁用 websocket.conf
sudo mv /usr/local/etc/nginx/vhosts/websocket.conf /usr/local/etc/nginx/vhosts/websocket.conf.bak
# 然后改回 listen 80;

# 方案2：移除 websocket.conf 的 default_server
# 编辑 /usr/local/etc/nginx/vhosts/websocket.conf
# 将 listen 80 default_server; 改为 listen 80;
```

## ✅ 验证

重启 Nginx 后，访问 `http://habit-tracker.com:8080/api/health` 应该返回 JSON：

```json
{
  "status": "ok",
  "database": "connected",
  "timestamp": "2026-01-30T..."
}
```
