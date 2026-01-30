# Nginx 配置说明

## 配置文件说明

- `nginx.conf` - HTTP 配置（开发/测试环境）
- `nginx-ssl.conf` - HTTPS 配置（生产环境推荐）

## 快速部署步骤

### 1. 复制配置文件

```bash
# 复制到 Nginx 配置目录
sudo cp nginx.conf /etc/nginx/sites-available/habit-tracker-api

# 或者直接编辑
sudo nano /etc/nginx/sites-available/habit-tracker-api
```

### 2. 修改配置参数

必须修改以下参数：

```nginx
server_name your-domain.com;  # 改为你的域名或 IP
root /path/to/habit-tracker/api/public;  # 改为实际项目路径
fastcgi_pass unix:/var/run/php/php-fpm.sock;  # 根据 PHP-FPM 配置修改
```

**查找 PHP-FPM Socket 路径：**

```bash
# 方法1：查看 PHP-FPM 配置
sudo cat /etc/php/7.4/fpm/pool.d/www.conf | grep listen

# 方法2：查看进程
ps aux | grep php-fpm

# 常见路径：
# Unix Socket: /var/run/php/php7.4-fpm.sock
# 或 /var/run/php-fpm/php-fpm.sock
# TCP: 127.0.0.1:9000
```

### 3. 创建符号链接

```bash
sudo ln -s /etc/nginx/sites-available/habit-tracker-api /etc/nginx/sites-enabled/
```

### 4. 测试配置

```bash
# 测试 Nginx 配置语法
sudo nginx -t

# 如果成功，会显示：
# nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
# nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### 5. 重启 Nginx

```bash
sudo systemctl reload nginx
# 或
sudo systemctl restart nginx
```

### 6. 设置文件权限

```bash
# 确保 Nginx 用户有读取权限
sudo chown -R www-data:www-data /path/to/habit-tracker/api
sudo chmod -R 755 /path/to/habit-tracker/api
sudo chmod -R 777 /path/to/habit-tracker/api/runtime  # ThinkPHP 需要写入权限
```

## 常见问题

### 1. 502 Bad Gateway

**原因：** PHP-FPM 未运行或 Socket 路径错误

**解决：**
```bash
# 检查 PHP-FPM 状态
sudo systemctl status php7.4-fpm

# 启动 PHP-FPM
sudo systemctl start php7.4-fpm

# 检查 Socket 文件是否存在
ls -l /var/run/php/php7.4-fpm.sock
```

### 2. 404 Not Found

**原因：** URL 重写规则未生效

**解决：**
- 确保 `if (!-e $request_filename)` 规则存在
- 检查 `try_files` 配置
- 确认 `root` 路径正确

### 3. 403 Forbidden

**原因：** 文件权限不足

**解决：**
```bash
# 设置正确的权限
sudo chown -R www-data:www-data /path/to/habit-tracker/api
sudo chmod -R 755 /path/to/habit-tracker/api/public
```

### 4. 无法访问 .env 文件

**原因：** 安全配置阻止访问

**解决：** 这是正常的安全行为，`.env` 文件不应该通过 Web 访问

## 性能优化建议

### 1. 启用 Gzip 压缩

在 `http` 块中添加：

```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
```

### 2. 启用缓存

```nginx
# 在 server 块中添加
proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=api_cache:10m max_size=100m inactive=60m;
```

### 3. 限制请求频率

```nginx
# 在 http 块中添加
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;

# 在 location / 中添加
limit_req zone=api_limit burst=20 nodelay;
```

## HTTPS 配置（生产环境）

### 1. 使用 Let's Encrypt 免费证书

```bash
# 安装 Certbot
sudo apt install certbot python3-certbot-nginx

# 自动配置 SSL
sudo certbot --nginx -d your-domain.com
```

### 2. 手动配置 SSL

参考 `nginx-ssl.conf` 文件，修改证书路径：

```nginx
ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
```

### 3. 自动续期

```bash
# 测试续期
sudo certbot renew --dry-run

# 添加到 crontab（每月自动续期）
sudo crontab -e
# 添加：0 0 1 * * certbot renew --quiet
```

## 日志查看

```bash
# 查看访问日志
sudo tail -f /var/log/nginx/habit-tracker-access.log

# 查看错误日志
sudo tail -f /var/log/nginx/habit-tracker-error.log

# 查看 PHP-FPM 日志
sudo tail -f /var/log/php7.4-fpm.log
```

## 测试 API

```bash
# 健康检查
curl http://your-domain.com/api/health

# 获取习惯列表
curl http://your-domain.com/api/habits

# 创建习惯
curl -X POST http://your-domain.com/api/habits \
  -H "Content-Type: application/json" \
  -d '{"name":"测试习惯","emoji":"📝","color":"#10b981"}'
```
