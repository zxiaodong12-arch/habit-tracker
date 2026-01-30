# 重启 Nginx 步骤

## 问题

配置已改为 8080 端口，但 Nginx 没有重新加载配置。

## 解决方案

### 方法1：使用 sudo 重启（推荐）

```bash
# 停止 Nginx
sudo nginx -s stop

# 启动 Nginx
sudo nginx

# 检查端口
lsof -i :8080 | grep nginx
```

### 方法2：使用 brew services

```bash
# 停止
brew services stop nginx

# 启动
brew services start nginx

# 检查状态
brew services list | grep nginx
```

### 方法3：强制重启

```bash
# 查找 Nginx 进程
ps aux | grep nginx

# 杀死进程（注意 PID）
sudo kill -9 <nginx_master_pid>

# 重新启动
sudo nginx
```

## 验证

重启后执行：

```bash
# 1. 检查端口是否监听
lsof -i :8080 | grep nginx

# 2. 测试 API
curl http://localhost:8080/api/health

# 3. 测试域名
curl http://habit-tracker.com:8080/api/health
```

## 如果还是不行

检查 Nginx 错误日志：

```bash
tail -20 /usr/local/var/log/nginx/error.log
```

检查配置是否正确加载：

```bash
nginx -T 2>&1 | grep -A 5 "listen.*8080"
```
