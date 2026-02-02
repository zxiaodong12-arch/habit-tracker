# 为什么不能监听 80 端口？

## 🔍 问题原因

### 1. 多个配置监听同一端口

你的 Nginx 配置目录中有**很多配置文件都在监听 80 端口**：

- `websocket.conf` - `listen 80 default_server;`
- `apicenter.conf` - `listen 80;`
- `apitest.conf` - `listen 80;`
- `dj_admin.conf` - `listen 80;`
- ... 还有更多

### 2. default_server 优先级

`websocket.conf` 中有 `default_server` 标记：

```nginx
listen 80 default_server;
```

这意味着：
- **所有没有明确匹配到其他 server_name 的请求**，都会被 `websocket.conf` 处理
- 即使你的 `habit-tracker.conf` 有正确的 `server_name habit-tracker.com`，如果 Nginx 没有正确匹配，就会使用 `default_server`

### 3. Nginx 的 server_name 匹配规则

Nginx 按以下顺序匹配：
1. 精确匹配 `server_name`
2. 通配符匹配
3. 正则表达式匹配
4. **如果没有匹配，使用 `default_server`**

## ✅ 解决方案

### 方案1：移除 websocket.conf 的 default_server（推荐）

```bash
# 备份原文件
sudo cp /usr/local/etc/nginx/vhosts/websocket.conf /usr/local/etc/nginx/vhosts/websocket.conf.bak

# 编辑文件，移除 default_server
sudo nano /usr/local/etc/nginx/vhosts/websocket.conf
# 将：listen 80 default_server;
# 改为：listen 80;

# 测试配置
nginx -t

# 重启 Nginx
sudo nginx -s reload
```

### 方案2：给 habit-tracker.conf 添加 default_server

修改 `/usr/local/etc/nginx/vhosts/habit-tracker.conf`：

```nginx
listen 80 default_server;
```

**注意：** 这会让所有未匹配的请求都转到你的 API，可能影响其他服务。

### 方案3：使用不同的 server_name 优先级

确保 `habit-tracker.com` 在 hosts 文件中正确配置，并且 Nginx 能正确匹配。

### 方案4：继续使用 8080 端口（最简单）

如果 80 端口冲突太多，继续使用 8080 端口是最简单的方案。

## 🚀 推荐操作步骤

### 如果你想使用 80 端口：

1. **检查 websocket.conf 是否真的需要 default_server**
   ```bash
   cat /usr/local/etc/nginx/vhosts/websocket.conf | grep -A 3 "server_name"
   ```

2. **如果不需要，移除 default_server**
   ```bash
   sudo sed -i '' 's/listen 80 default_server;/listen 80;/' /usr/local/etc/nginx/vhosts/websocket.conf
   ```

3. **修改 habit-tracker.conf 改回 80 端口**
   ```bash
   sudo sed -i '' 's/listen 8080;/listen 80;/' /usr/local/etc/nginx/vhosts/habit-tracker.conf
   ```

4. **测试并重启**
   ```bash
   nginx -t
   sudo nginx -s reload
   ```

5. **测试**
   ```bash
   curl http://habit-tracker.com/api/health
   ```

## 📋 检查当前状态

```bash
# 查看所有监听 80 端口的配置
grep -r "listen.*80" /usr/local/etc/nginx/vhosts/*.conf | grep -v "^#"

# 查看哪些有 default_server
grep -r "default_server" /usr/local/etc/nginx/vhosts/*.conf

# 查看实际监听的端口
sudo lsof -i :80 | grep nginx
```

## ⚠️ 注意事项

- 修改配置前先备份
- 确保不影响其他正在运行的服务
- 如果 websocket 服务正在使用，移除 default_server 前先确认
