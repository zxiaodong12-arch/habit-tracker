# macOS 上配置 Nginx

## 安装 Nginx

### 使用 Homebrew（推荐）

```bash
# 安装 Nginx
brew install nginx

# 启动 Nginx
brew services start nginx

# 或者手动启动
nginx
```

## 配置文件位置

- **配置文件：** `/opt/homebrew/etc/nginx/nginx.conf`（Apple Silicon）
- **配置文件：** `/usr/local/etc/nginx/nginx.conf`（Intel）
- **网站目录：** `/opt/homebrew/var/www` 或 `/usr/local/var/www`

## 部署步骤

### 1. 创建站点配置

```bash
# 编辑主配置文件或创建站点配置
sudo nano /opt/homebrew/etc/nginx/nginx.conf
# 或
sudo nano /usr/local/etc/nginx/nginx.conf
```

在 `http` 块中添加：

```nginx
include /path/to/habit-tracker/api/nginx.conf;
```

或者直接在 `http` 块中粘贴配置内容。

### 2. 修改配置参数

编辑 `nginx.conf`，修改以下参数：

```nginx
server_name localhost;  # 或你的域名
root /path/to/habit-tracker/api/public;  # 改为实际路径
fastcgi_pass 127.0.0.1:9000;  # macOS 通常使用 TCP 方式
```

### 3. 测试配置

```bash
# 测试 Nginx 配置语法
nginx -t

# 如果成功，会显示：
# nginx: the configuration file /opt/homebrew/etc/nginx/nginx.conf syntax is ok
# nginx: configuration file /opt/homebrew/etc/nginx/nginx.conf test is successful
```

### 4. 重启 Nginx

```bash
# 方法1：使用 brew services（推荐）
brew services restart nginx

# 方法2：使用 nginx 命令
sudo nginx -s reload
# 或
sudo nginx -s stop
sudo nginx

# 方法3：使用 killall
sudo killall nginx
sudo nginx
```

### 5. 设置文件权限

```bash
# 确保 Nginx 用户有读取权限
sudo chown -R $(whoami):staff /path/to/habit-tracker/api
sudo chmod -R 755 /path/to/habit-tracker/api
sudo chmod -R 777 /path/to/habit-tracker/api/runtime
```

## PHP-FPM 配置

### 安装 PHP-FPM

```bash
# 安装 PHP（包含 PHP-FPM）
brew install php

# 启动 PHP-FPM
brew services start php

# 查看 PHP-FPM 配置
cat /opt/homebrew/etc/php/8.2/php-fpm.d/www.conf | grep listen
# 或
cat /usr/local/etc/php/8.2/php-fpm.d/www.conf | grep listen
```

### 常见 PHP-FPM 配置

macOS 上通常使用 TCP 方式：

```nginx
fastcgi_pass 127.0.0.1:9000;
```

如果使用 Unix Socket：

```nginx
fastcgi_pass unix:/opt/homebrew/var/run/php-fpm.sock;
```

## 常用命令

### 查看 Nginx 状态

```bash
# 查看进程
ps aux | grep nginx

# 查看端口
lsof -i :80
lsof -i :8080
```

### 查看日志

```bash
# 访问日志
tail -f /opt/homebrew/var/log/nginx/access.log
# 或
tail -f /usr/local/var/log/nginx/access.log

# 错误日志
tail -f /opt/homebrew/var/log/nginx/error.log
# 或
tail -f /usr/local/var/log/nginx/error.log
```

### 停止/启动 Nginx

```bash
# 停止
brew services stop nginx
# 或
sudo nginx -s stop

# 启动
brew services start nginx
# 或
sudo nginx

# 重启
brew services restart nginx
# 或
sudo nginx -s reload
```

## 常见问题

### 1. 端口被占用

```bash
# 查看占用 80 端口的进程
sudo lsof -i :80

# 杀死进程
sudo kill -9 <PID>
```

### 2. 权限问题

```bash
# 如果 Nginx 无法读取文件，检查权限
ls -la /path/to/habit-tracker/api/public

# 修改权限
sudo chmod -R 755 /path/to/habit-tracker/api/public
```

### 3. PHP-FPM 未运行

```bash
# 检查 PHP-FPM 状态
brew services list | grep php

# 启动 PHP-FPM
brew services start php

# 手动启动
php-fpm
```

### 4. 配置文件路径问题

确认你的 Mac 是 Apple Silicon 还是 Intel：

```bash
# 查看架构
uname -m
# arm64 = Apple Silicon
# x86_64 = Intel
```

- **Apple Silicon：** `/opt/homebrew/`
- **Intel：** `/usr/local/`

## 测试 API

```bash
# 健康检查
curl http://localhost/api/health

# 获取习惯列表
curl http://localhost/api/habits

# 创建习惯
curl -X POST http://localhost/api/habits \
  -H "Content-Type: application/json" \
  -d '{"name":"测试习惯","emoji":"📝","color":"#10b981"}'
```

## 开发环境建议

### 使用自定义端口（避免权限问题）

```nginx
server {
    listen 8080;  # 使用 8080 端口，不需要 sudo
    server_name localhost;
    # ... 其他配置
}
```

然后访问：`http://localhost:8080/api/health`

### 启用详细错误日志

在 `nginx.conf` 中：

```nginx
error_log /opt/homebrew/var/log/nginx/error.log debug;
```
