# 腾讯云部署指南

本指南将帮助你将 habit-tracker 项目部署到腾讯云，包括前端、后端 API 和数据库的完整部署流程。

## 📋 部署架构

```
用户浏览器
    ↓
[前端] → 腾讯云 COS 静态网站托管 或 CVM Nginx
    ↓
[后端 API] → CVM (Nginx + PHP-FPM + ThinkPHP)
    ↓
[数据库] → 腾讯云 MySQL (云数据库) 或 CVM 自建 MySQL
```

## 🎯 方案选择

### 方案一：COS + CVM（推荐）
- **前端**：部署到腾讯云 COS 静态网站托管（成本低、CDN 加速）
- **后端**：部署到 CVM（云服务器）
- **数据库**：腾讯云 MySQL 或 CVM 自建 MySQL

### 方案二：全 CVM
- **前端 + 后端**：都部署到同一台 CVM
- **数据库**：腾讯云 MySQL 或 CVM 自建 MySQL

本指南以**方案一**为主，同时提供方案二的配置说明。

---

## 📦 准备工作

### 1. 购买腾讯云资源

#### 必需资源：
- ✅ **CVM 云服务器**（推荐配置：2核4G，CentOS 7.6+ 或 Ubuntu 20.04+）
- ✅ **MySQL 数据库**（可选：云数据库 MySQL 或 CVM 自建）
- ✅ **COS 对象存储**（用于前端静态文件，可选）

#### 可选资源：
- 🌐 **域名**（用于访问，可选）
- 🔒 **SSL 证书**（用于 HTTPS，可选）

### 2. 本地环境准备

确保本地已安装：
- Node.js 16+ 和 npm
- Git
- SSH 客户端（用于连接服务器）

---

## 🚀 部署步骤

### 第一步：准备服务器环境

#### 1.1 连接服务器

```bash
ssh root@你的服务器IP
```

#### 1.2 安装基础软件

**CentOS 7.x / OpenCloudOS:**
```bash
# 更新系统
yum update -y

# 安装 EPEL 仓库（OpenCloudOS 需要）
yum install -y epel-release

# 安装 Nginx
yum install -y nginx

# 安装 PHP 8.0+ 和 PHP-FPM
yum install -y epel-release
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum install -y php80 php80-php-fpm php80-php-mysql php80-php-mbstring php80-php-xml php80-php-curl php80-php-zip

# 安装 MySQL（如果使用自建数据库）
yum install -y mariadb-server mariadb
systemctl start mariadb
systemctl enable mariadb

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

**Ubuntu 20.04+:**
```bash
# 更新系统
apt update && apt upgrade -y

# 安装 Nginx
apt install -y nginx

# 安装 PHP 8.0+ 和 PHP-FPM
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.0-fpm php8.0-mysql php8.0-mbstring php8.0-xml php8.0-curl php8.0-zip

# 安装 MySQL（如果使用自建数据库）
apt install -y mysql-server
systemctl start mysql
systemctl enable mysql

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

#### 1.3 启动服务

```bash
# 启动 Nginx
systemctl start nginx
systemctl enable nginx

# 启动 PHP-FPM
systemctl start php-fpm  # CentOS
# 或
systemctl start php8.0-fpm  # Ubuntu
systemctl enable php-fpm  # CentOS
# 或
systemctl enable php8.0-fpm  # Ubuntu

# 检查服务状态
systemctl status nginx
systemctl status php-fpm  # 或 php8.0-fpm
```

---

### 第二步：部署数据库

#### 2.1 使用腾讯云 MySQL（推荐）

1. 在腾讯云控制台创建 MySQL 实例
2. 记录以下信息：
   - 数据库地址（内网 IP）
   - 端口（默认 3306）
   - 用户名和密码
   - 数据库名（如：`habit_tracker`）

#### 2.2 使用 CVM 自建 MySQL

```bash
# 登录 MySQL（首次登录需要设置 root 密码）
mysql_secure_installation

# 创建数据库和用户
mysql -u root -p
```

```sql
-- 创建数据库
CREATE DATABASE habit_tracker DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户（可选，建议使用独立用户）
CREATE USER 'habit_user'@'localhost' IDENTIFIED BY '你的密码';
GRANT ALL PRIVILEGES ON habit_tracker.* TO 'habit_user'@'localhost';
FLUSH PRIVILEGES;

-- 退出
EXIT;
```

#### 2.3 导入数据库结构

```bash
# 将 database.sql 上传到服务器
# 方法1：使用 scp
scp database.sql root@你的服务器IP:/tmp/

# 方法2：直接在服务器上下载（如果代码在 Git 仓库）
cd /tmp
wget https://你的仓库地址/raw/main/database.sql

# 导入数据库
mysql -u root -p habit_tracker < /tmp/database.sql
# 或使用独立用户
mysql -u habit_user -p habit_tracker < /tmp/database.sql
```

---

### 第三步：部署后端 API

#### 3.1 上传代码到服务器

```bash
# 在服务器上创建项目目录
mkdir -p /var/www/habit-tracker
cd /var/www/habit-tracker

# 方法1：使用 Git（推荐）
git clone https://你的仓库地址.git .
cd api

# 方法2：使用 scp 上传
# 在本地执行：
# scp -r api root@你的服务器IP:/var/www/habit-tracker/
```

#### 3.2 安装 PHP 依赖

```bash
cd /var/www/habit-tracker/api

# 安装 Composer 依赖
composer install --no-dev --optimize-autoloader
```

#### 3.3 配置环境变量

```bash
# 复制环境变量模板
cp env.example .env

# 编辑配置文件
vi .env
```

修改 `.env` 文件内容：

```ini
APP_DEBUG = false
APP_TRACE = false

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1  # 或腾讯云 MySQL 内网 IP
DATABASE = habit_tracker
USERNAME = root  # 或你创建的用户名
PASSWORD = 你的数据库密码
HOSTPORT = 3306
CHARSET = utf8mb4
DEBUG = false

[LANG]
default_lang = zh-cn
```

#### 3.4 设置目录权限

```bash
# 设置 runtime 目录权限
chmod -R 777 runtime
chown -R nginx:nginx runtime  # CentOS
# 或
chown -R www-data:www-data runtime  # Ubuntu
```

#### 3.5 配置 Nginx

创建 Nginx 配置文件：

```bash
vi /etc/nginx/conf.d/habit-tracker-api.conf
```

**内容如下（请根据实际情况修改路径和域名）：**

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;  # 修改为你的 API 域名或 IP
    
    root /var/www/habit-tracker/api/public;
    index index.php index.html;
    
    # 字符集
    charset utf-8;
    
    # 日志配置
    access_log /var/log/nginx/habit-tracker-api-access.log;
    error_log /var/log/nginx/habit-tracker-api-error.log;
    
    # 客户端上传文件大小限制
    client_max_body_size 10M;
    
    # 主要 location 块
    location / {
        # ThinkPHP URL 重写
        try_files $uri $uri/ /index.php?s=$uri&$args;
    }
    
    # PHP 文件处理
    location ~ \.php$ {
        # FastCGI 配置（Linux 使用 Unix Socket）
        fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;  # CentOS
        # 或
        # fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;  # Ubuntu
        
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

**检查并重载 Nginx：**

```bash
# 测试配置
nginx -t

# 重载配置
systemctl reload nginx
```

#### 3.6 测试 API

```bash
# 测试健康检查接口
curl http://你的服务器IP/api/health

# 应该返回 JSON 响应
```

---

### 第四步：部署前端

#### 方案 A：部署到腾讯云 COS（推荐）

##### 4.1 构建前端

在本地执行：

```bash
cd vue-frontend

# 安装依赖
npm install

# 修改 API 地址（如果需要）
# 编辑 src/services/api.js，将 baseURL 改为生产环境地址
# const baseURL = 'https://api.yourdomain.com/api'

# 构建生产版本
npm run build
```

构建完成后，`dist` 目录会生成静态文件。

##### 4.2 上传到 COS

1. **创建 COS 存储桶**
   - 登录腾讯云控制台 → 对象存储 COS
   - 创建存储桶，选择**静态网站托管**
   - 记录存储桶名称和访问域名

2. **上传文件**
   - 使用 COS 控制台上传 `dist` 目录下的所有文件
   - 或使用 COS CLI 工具：
   ```bash
   # 安装 COS CLI
   pip install coscmd
   
   # 配置
   coscmd config -a SecretId -s SecretKey -b 存储桶名 -r 地域
   
   # 上传
   cd dist
   coscmd upload -rs . /
   ```

3. **配置静态网站托管**
   - 在 COS 控制台 → 基础配置 → 静态网站
   - 开启静态网站托管
   - 设置默认首页：`index.html`
   - 设置错误页面：`index.html`（用于 Vue Router 的 history 模式）

4. **配置 CDN（可选）**
   - 在 CDN 控制台添加域名
   - 源站选择 COS 存储桶
   - 配置 HTTPS 证书

##### 4.3 修改前端 API 地址

如果前端部署在 COS，需要修改 API 地址：

**方法1：使用环境变量（推荐）**

创建 `vue-frontend/.env.production`：

```env
VITE_API_BASE_URL=https://api.yourdomain.com/api
```

修改 `vue-frontend/src/services/api.js`：

```javascript
const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api'
```

**方法2：直接修改**

编辑 `vue-frontend/src/services/api.js`：

```javascript
const baseURL = 'https://api.yourdomain.com/api'  // 修改为你的 API 地址
```

然后重新构建：

```bash
npm run build
```

#### 方案 B：部署到 CVM Nginx

##### 4.1 构建前端（同上）

##### 4.2 上传到服务器

```bash
# 在服务器上创建前端目录
mkdir -p /var/www/habit-tracker/frontend

# 使用 scp 上传 dist 目录内容
# 在本地执行：
cd vue-frontend
scp -r dist/* root@你的服务器IP:/var/www/habit-tracker/frontend/
```

##### 4.3 配置 Nginx

创建前端 Nginx 配置：

```bash
vi /etc/nginx/conf.d/habit-tracker-frontend.conf
```

**内容如下：**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;  # 修改为你的域名
    
    root /var/www/habit-tracker/frontend;
    index index.html;
    
    # 字符集
    charset utf-8;
    
    # 日志配置
    access_log /var/log/nginx/habit-tracker-frontend-access.log;
    error_log /var/log/nginx/habit-tracker-frontend-error.log;
    
    # Vue Router history 模式支持
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # 静态资源缓存
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
}
```

**重载 Nginx：**

```bash
nginx -t
systemctl reload nginx
```

---

### 第五步：配置域名和 HTTPS（可选但推荐）

#### 5.1 配置域名解析

在域名服务商处添加 DNS 记录：

```
类型    主机记录    记录值
A       @          你的服务器IP
A       www        你的服务器IP
A       api        你的服务器IP（如果 API 使用独立域名）
```

#### 5.2 配置 SSL 证书

**使用腾讯云 SSL 证书（免费）：**

1. 在腾讯云控制台申请免费 SSL 证书
2. 下载证书文件（Nginx 版本）
3. 上传到服务器：

```bash
mkdir -p /etc/nginx/ssl
# 上传证书文件到 /etc/nginx/ssl/
```

4. 修改 Nginx 配置，添加 HTTPS：

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    # SSL 证书配置
    ssl_certificate /etc/nginx/ssl/yourdomain.com.crt;
    ssl_certificate_key /etc/nginx/ssl/yourdomain.com.key;
    
    # SSL 优化配置
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # ... 其他配置同上
}

# HTTP 重定向到 HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

5. 重载 Nginx：

```bash
nginx -t
systemctl reload nginx
```

#### 5.3 更新前端 API 地址

如果使用 HTTPS，需要更新前端 API 地址为 `https://`。

---

## 🔧 配置防火墙

```bash
# CentOS 7
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload

# Ubuntu
ufw allow 80/tcp
ufw allow 443/tcp
ufw reload
```

---

## ✅ 验证部署

### 1. 检查服务状态

```bash
# 检查 Nginx
systemctl status nginx

# 检查 PHP-FPM
systemctl status php-fpm  # 或 php8.0-fpm

# 检查 MySQL
systemctl status mysql  # 或 mariadb
```

### 2. 测试 API

```bash
# 测试健康检查
curl http://你的API地址/api/health

# 测试注册接口
curl -X POST http://你的API地址/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"123456"}'
```

### 3. 访问前端

在浏览器中访问：
- 前端地址：`http://yourdomain.com` 或 `https://yourdomain.com`
- API 地址：`http://api.yourdomain.com` 或 `https://api.yourdomain.com`

---

## 🐛 常见问题

### 1. 502 Bad Gateway

**原因：** PHP-FPM 未启动或配置错误

**解决：**
```bash
# 检查 PHP-FPM 状态
systemctl status php-fpm

# 检查 PHP-FPM socket 路径
ls -la /var/run/php-fpm/  # CentOS
# 或
ls -la /var/run/php/  # Ubuntu

# 确保 Nginx 配置中的 fastcgi_pass 路径正确
```

### 2. 404 Not Found

**原因：** ThinkPHP 路由未正确配置

**解决：**
- 检查 Nginx 的 `try_files` 配置
- 检查 `api/public/.htaccess` 或 Nginx 重写规则
- 检查 `api/config/route.php` 路由配置

### 3. 数据库连接失败

**原因：** 数据库配置错误或权限问题

**解决：**
```bash
# 测试数据库连接
mysql -h 数据库地址 -u 用户名 -p

# 检查 .env 文件配置
cat /var/www/habit-tracker/api/.env

# 检查数据库用户权限
mysql -u root -p
GRANT ALL PRIVILEGES ON habit_tracker.* TO '用户名'@'%';
FLUSH PRIVILEGES;
```

### 4. CORS 跨域问题

**原因：** 前端和 API 不在同一域名

**解决：** 在 ThinkPHP 中添加 CORS 中间件，或配置 Nginx：

```nginx
# 在 API 的 Nginx 配置中添加
add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Methods 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
add_header Access-Control-Allow-Headers 'Authorization, Content-Type';
```

### 5. 前端路由 404

**原因：** Vue Router history 模式需要服务器支持

**解决：** 确保 Nginx 配置了 `try_files $uri $uri/ /index.html;`

---

## 📝 维护建议

### 1. 定期备份

```bash
# 备份数据库
mysqldump -u root -p habit_tracker > /backup/habit_tracker_$(date +%Y%m%d).sql

# 备份代码
tar -czf /backup/habit-tracker-code_$(date +%Y%m%d).tar.gz /var/www/habit-tracker/
```

### 2. 日志监控

```bash
# 查看 Nginx 错误日志
tail -f /var/log/nginx/habit-tracker-api-error.log

# 查看 PHP 错误日志
tail -f /var/log/php-fpm/error.log  # CentOS
# 或
tail -f /var/log/php8.0-fpm.log  # Ubuntu
```

### 3. 性能优化

- 启用 Nginx 缓存
- 配置 PHP OPcache
- 使用 Redis 缓存（可选）
- 配置 CDN 加速静态资源

---

## 🎉 部署完成！

现在你的 habit-tracker 应用已经成功部署到腾讯云了！

**访问地址：**
- 前端：`https://yourdomain.com`
- API：`https://api.yourdomain.com`

**下一步：**
1. 注册第一个账号
2. 添加你的第一个习惯
3. 开始追踪你的习惯！

如有问题，请查看日志文件或联系技术支持。
