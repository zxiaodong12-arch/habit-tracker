# Nginx 配置错误修复指南

## 错误说明

错误信息：`"server" directive is not allowed here`

**原因：** `server` 块不能直接放在 `nginx.conf` 主配置文件中，必须在 `http` 块内。

## 解决方案

### 方法一：检查主配置文件结构

```bash
# 查看主配置文件
cat /www/server/nginx/conf/nginx.conf
```

主配置文件应该类似这样：

```nginx
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # 这里可以包含其他配置文件
    include /etc/nginx/conf.d/*.conf;
    # 或
    include /www/server/nginx/conf/vhost/*.conf;
    
    # server 块应该在这里，或者通过 include 引入
}
```

### 方法二：使用独立的配置文件（推荐）

**不要直接修改主配置文件**，而是创建独立的配置文件：

#### 1. 创建配置文件目录（如果不存在）

```bash
# 检查是否有 conf.d 目录
ls -la /www/server/nginx/conf/

# 如果有 vhost 目录（宝塔面板）
mkdir -p /www/server/nginx/conf/vhost

# 或创建 conf.d 目录
mkdir -p /etc/nginx/conf.d
```

#### 2. 创建独立的配置文件

```bash
# 如果使用宝塔面板的路径
vi /www/server/nginx/conf/vhost/habit-tracker-api.conf

# 或使用标准路径
vi /etc/nginx/conf.d/habit-tracker-api.conf
```

#### 3. 配置文件内容

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;  # 修改为你的域名或 IP
    
    root /var/www/habit-tracker/api/public;
    index index.php index.html;
    
    # 字符集
    charset utf-8;
    
    # 日志配置
    access_log /www/server/nginx/logs/habit-tracker-api-access.log;
    error_log /www/server/nginx/logs/habit-tracker-api-error.log;
    
    # 客户端上传文件大小限制
    client_max_body_size 10M;
    
    # 主要 location 块
    location / {
        # ThinkPHP URL 重写
        try_files $uri $uri/ /index.php?s=$uri&$args;
    }
    
    # PHP 文件处理
    location ~ \.php$ {
        # FastCGI 配置
        # 宝塔面板通常使用 Unix Socket
        fastcgi_pass unix:/tmp/php-cgi-74.sock;  # 根据你的 PHP 版本调整
        # 或使用 TCP
        # fastcgi_pass 127.0.0.1:9000;
        
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
        
        # FastCGI 超时设置
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
        
        # FastCGI 缓冲区设置
        fastcgi_buffer_size 64k;
        fastcgi_buffers 4 64k;
        fastcgi_busy_buffers_size 128k;
    }
    
    # 静态文件处理
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # 禁止访问敏感文件
    location ~* \.(env|log|sql|md|git)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # 禁止访问 vendor 和 runtime 目录
    location ~ ^/(vendor|runtime)/ {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

#### 4. 确保主配置文件包含这个文件

检查主配置文件 `/www/server/nginx/conf/nginx.conf` 的 `http` 块中是否有：

```nginx
http {
    # ... 其他配置 ...
    
    # 包含 vhost 目录下的配置（宝塔面板）
    include /www/server/nginx/conf/vhost/*.conf;
    
    # 或包含 conf.d 目录下的配置（标准方式）
    include /etc/nginx/conf.d/*.conf;
}
```

如果没有，需要添加。

### 方法三：修复主配置文件（如果必须修改主配置）

如果主配置文件结构有问题，需要修复：

```bash
# 备份原配置
cp /www/server/nginx/conf/nginx.conf /www/server/nginx/conf/nginx.conf.bak

# 编辑配置文件
vi /www/server/nginx/conf/nginx.conf
```

确保结构正确：

```nginx
# 全局配置
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

# http 块开始
http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # 日志格式
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    
    # server 块必须在这里，或通过 include 引入
    include /www/server/nginx/conf/vhost/*.conf;
    
    # 如果有默认 server 块，也在这里
    # server {
    #     listen 80 default_server;
    #     ...
    # }
}

# http 块结束
```

## 验证配置

```bash
# 测试配置文件语法
nginx -t

# 如果成功，会显示：
# nginx: the configuration file /www/server/nginx/conf/nginx.conf syntax is ok
# nginx: configuration file /www/server/nginx/conf/nginx.conf test is successful

# 重载配置
nginx -s reload
# 或
systemctl reload nginx
```

## 查找 PHP-FPM Socket 路径

如果是宝塔面板，需要找到正确的 PHP-FPM socket 路径：

```bash
# 查找 PHP-FPM socket
find /tmp -name "php-cgi*.sock" 2>/dev/null
# 或
ls -la /tmp/php-cgi*.sock

# 查看 PHP 版本
php -v

# 宝塔面板的 PHP-FPM socket 通常在：
# /tmp/php-cgi-74.sock  (PHP 7.4)
# /tmp/php-cgi-80.sock  (PHP 8.0)
# /tmp/php-cgi-81.sock  (PHP 8.1)
```

## 常见问题

### Q: 如何知道使用哪个配置文件目录？

**A:** 检查主配置文件：

```bash
grep -r "include.*\.conf" /www/server/nginx/conf/nginx.conf
```

### Q: 宝塔面板如何配置？

**A:** 如果使用宝塔面板：
1. 登录宝塔面板
2. 网站 → 添加站点
3. 或直接在 `/www/server/nginx/conf/vhost/` 目录下创建配置文件
4. 在宝塔面板中重载 Nginx

### Q: 配置文件不生效？

**A:** 
1. 确保配置文件在正确的目录
2. 确保主配置文件包含了该目录
3. 检查文件权限：`chmod 644 /www/server/nginx/conf/vhost/habit-tracker-api.conf`
4. 重载 Nginx：`nginx -s reload`

## 快速修复步骤

```bash
# 1. 创建配置文件目录（如果不存在）
mkdir -p /www/server/nginx/conf/vhost

# 2. 创建配置文件
cat > /www/server/nginx/conf/vhost/habit-tracker-api.conf << 'EOF'
server {
    listen 80;
    server_name _;  # 使用默认
    
    root /var/www/habit-tracker/api/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?s=$uri&$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-74.sock;  # 根据实际情况修改
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

# 3. 确保主配置文件包含 vhost 目录
# 编辑 /www/server/nginx/conf/nginx.conf
# 在 http 块中添加：include /www/server/nginx/conf/vhost/*.conf;

# 4. 测试配置
nginx -t

# 5. 重载配置
nginx -s reload
```
