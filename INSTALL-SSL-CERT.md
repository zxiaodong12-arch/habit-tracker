# API 服务器 SSL 证书安装指南

## 安装 Certbot（Let's Encrypt）

### OpenCloudOS / CentOS 7

```bash
# 1. 安装 EPEL 仓库（如果未安装）
sudo yum install -y epel-release

# 2. 安装 certbot 和 nginx 插件
sudo yum install -y certbot python3-certbot-nginx

# 3. 验证安装
certbot --version
```

### Ubuntu 20.04+

```bash
# 1. 更新包列表
sudo apt update

# 2. 安装 certbot 和 nginx 插件
sudo apt install -y certbot python3-certbot-nginx

# 3. 验证安装
certbot --version
```

## 重要提示：IP 地址无法申请 SSL 证书

⚠️ **Let's Encrypt 不支持为 IP 地址申请证书**，只能为域名申请。

如果你的 API 服务器只有 IP 地址（`1.15.12.78`），需要：

### 方案一：使用域名（推荐）

1. **购买或使用现有域名**
   - 例如：`api.yourdomain.com`

2. **配置域名解析**
   - 在域名服务商处添加 A 记录：
     ```
     类型: A
     主机记录: api
     记录值: 1.15.12.78
     ```

3. **等待 DNS 生效**（通常几分钟到几小时）

4. **申请证书**
   ```bash
   sudo certbot --nginx -d api.yourdomain.com
   ```

### 方案二：使用自签名证书（仅用于测试）

如果只是测试，可以使用自签名证书：

```bash
# 1. 创建证书目录
sudo mkdir -p /etc/nginx/ssl

# 2. 生成自签名证书（有效期 365 天）
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/nginx/ssl/api.key \
  -out /etc/nginx/ssl/api.crt \
  -subj "/C=CN/ST=State/L=City/O=Organization/CN=1.15.12.78"

# 3. 设置权限
sudo chmod 600 /etc/nginx/ssl/api.key
sudo chmod 644 /etc/nginx/ssl/api.crt
```

**注意：** 自签名证书会在浏览器中显示警告，需要用户手动接受。

### 方案三：使用 CloudBase 云函数（推荐用于生产环境）

如果无法配置域名，可以考虑：
1. 将 API 迁移到 CloudBase 云函数
2. CloudBase 自动提供 HTTPS 支持
3. 无需配置 SSL 证书

## 使用域名申请证书的完整步骤

### 步骤 1: 配置域名解析

在域名服务商（如腾讯云、阿里云）处：

1. 登录域名控制台
2. 找到你的域名
3. 添加 A 记录：
   ```
   记录类型: A
   主机记录: api（或其他子域名）
   记录值: 1.15.12.78
   TTL: 600（或默认值）
   ```
4. 保存并等待生效

### 步骤 2: 验证域名解析

```bash
# 检查域名是否解析到正确的 IP
nslookup api.yourdomain.com
# 或
dig api.yourdomain.com

# 应该返回: 1.15.12.78
```

### 步骤 3: 配置 Nginx（确保 HTTP 正常）

确保 Nginx 配置正确，HTTP 可以正常访问：

```bash
# 检查 Nginx 配置
sudo nginx -t

# 如果配置有误，编辑配置文件
sudo vi /etc/nginx/conf.d/habit-tracker-api.conf
```

确保配置类似：

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;  # 使用域名，不是 IP
    
    root /var/www/habit-tracker/api/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?s=$uri&$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 步骤 4: 申请 SSL 证书

```bash
# 使用 certbot 自动配置
sudo certbot --nginx -d api.yourdomain.com

# 按提示操作：
# 1. 输入邮箱地址（用于证书到期提醒）
# 2. 同意服务条款
# 3. 选择是否分享邮箱（可选）
# 4. 选择是否重定向 HTTP 到 HTTPS（推荐选择 2：重定向）
```

Certbot 会自动：
- 申请证书
- 配置 Nginx SSL
- 设置自动续期

### 步骤 5: 验证证书

```bash
# 测试 HTTPS 访问
curl https://api.yourdomain.com/api/health

# 检查证书信息
sudo certbot certificates
```

### 步骤 6: 测试自动续期

```bash
# 测试续期（不会真正续期，只是测试）
sudo certbot renew --dry-run
```

## 配置前端使用 HTTPS API

### 方法一：使用环境变量（推荐）

创建 `vue-frontend/.env.production`：

```env
VITE_API_BASE_URL=https://api.yourdomain.com/api
```

### 方法二：直接修改代码

编辑 `vue-frontend/src/services/api.js`：

```javascript
const baseURL = import.meta.env.VITE_API_BASE_URL || 'https://api.yourdomain.com/api'
```

### 重新构建和部署

```bash
cd vue-frontend
npm run build
# 重新上传 dist 目录到 CloudBase
```

## 常见问题

### Q: 域名解析需要多长时间？

**A:** 通常几分钟到几小时，最长 48 小时。可以使用 `nslookup` 或 `dig` 命令检查。

### Q: 证书有效期多长？

**A:** Let's Encrypt 证书有效期 90 天，certbot 会自动续期。

### Q: 如何手动续期？

**A:** 
```bash
sudo certbot renew
```

### Q: 如果只有 IP 地址怎么办？

**A:** 
1. 购买域名（最便宜的一年几十元）
2. 或使用自签名证书（仅测试）
3. 或使用 CloudBase 云函数

### Q: 自签名证书如何配置 Nginx？

**A:** 

```nginx
server {
    listen 443 ssl http2;
    server_name 1.15.12.78;
    
    ssl_certificate /etc/nginx/ssl/api.crt;
    ssl_certificate_key /etc/nginx/ssl/api.key;
    
    # SSL 配置
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # ... 其他配置
}

# HTTP 重定向到 HTTPS
server {
    listen 80;
    server_name 1.15.12.78;
    return 301 https://$server_name$request_uri;
}
```

## 快速检查清单

- [ ] 已安装 certbot
- [ ] 已配置域名解析（A 记录指向 API 服务器 IP）
- [ ] 域名解析已生效（`nslookup` 可以解析）
- [ ] Nginx HTTP 配置正常
- [ ] 已申请 SSL 证书（`certbot --nginx -d api.yourdomain.com`）
- [ ] HTTPS 可以访问（`curl https://api.yourdomain.com/api/health`）
- [ ] 前端已配置 HTTPS API 地址
- [ ] 已重新构建和部署前端

## 总结

**核心步骤：**
1. 安装 certbot
2. 配置域名解析（必须，IP 无法申请证书）
3. 使用 certbot 申请证书
4. 配置前端使用 HTTPS API
5. 重新构建和部署

**如果只有 IP 地址：**
- 购买域名（推荐）
- 或使用自签名证书（仅测试）
- 或使用 CloudBase 云函数
