# 部署检查清单

使用此清单确保部署过程完整无误。

## 📋 部署前准备

### 服务器资源
- [ ] 已购买腾讯云 CVM 云服务器
- [ ] 已购买/配置 MySQL 数据库（云数据库或自建）
- [ ] 已购买 COS 存储桶（如果使用 COS 部署前端）
- [ ] 已准备域名（可选但推荐）
- [ ] 已申请 SSL 证书（可选但推荐）

### 本地环境
- [ ] 已安装 Node.js 16+ 和 npm
- [ ] 已安装 Git
- [ ] 已安装 SSH 客户端
- [ ] 已获取服务器 SSH 密钥或密码

---

## 🔧 服务器环境配置

### 基础软件安装
- [ ] Nginx 已安装并启动
- [ ] PHP 8.0+ 已安装
- [ ] PHP-FPM 已安装并启动
- [ ] MySQL/MariaDB 已安装并启动（如果自建）
- [ ] Composer 已安装

### 服务状态检查
- [ ] `systemctl status nginx` - 状态正常
- [ ] `systemctl status php-fpm` - 状态正常
- [ ] `systemctl status mysql` - 状态正常（如果自建）

---

## 💾 数据库配置

### 数据库创建
- [ ] 已创建数据库 `habit_tracker`
- [ ] 已创建数据库用户（如果使用独立用户）
- [ ] 已授予用户权限

### 数据导入
- [ ] 已上传 `database.sql` 到服务器
- [ ] 已执行 `mysql -u root -p habit_tracker < database.sql`
- [ ] 已验证表结构（`SHOW TABLES;`）

---

## 🔌 后端 API 部署

### 代码部署
- [ ] 已上传 API 代码到 `/var/www/habit-tracker/api/`
- [ ] 已执行 `composer install --no-dev`
- [ ] 已创建 `.env` 文件
- [ ] 已配置数据库连接信息

### 环境变量配置
- [ ] `APP_DEBUG = false`（生产环境）
- [ ] 数据库 `HOSTNAME` 已配置
- [ ] 数据库 `DATABASE` 已配置
- [ ] 数据库 `USERNAME` 已配置
- [ ] 数据库 `PASSWORD` 已配置
- [ ] 数据库 `HOSTPORT` 已配置

### 目录权限
- [ ] `runtime` 目录权限已设置（777 或 755）
- [ ] `runtime` 目录所有者已设置（nginx:nginx 或 www-data:www-data）

### Nginx 配置
- [ ] 已创建 `/etc/nginx/conf.d/habit-tracker-api.conf`
- [ ] `root` 路径已正确配置
- [ ] `fastcgi_pass` 路径已正确配置
- [ ] 已执行 `nginx -t` 测试配置
- [ ] 已执行 `systemctl reload nginx`

### API 测试
- [ ] `curl http://你的API地址/api/health` - 返回正常
- [ ] 测试注册接口 - 返回正常
- [ ] 测试登录接口 - 返回正常

---

## 🎨 前端部署

### 构建前端
- [ ] 已进入 `vue-frontend` 目录
- [ ] 已执行 `npm install`
- [ ] 已配置 API 地址（环境变量或直接修改）
- [ ] 已执行 `npm run build`
- [ ] 构建成功，`dist` 目录已生成

### 方案 A: COS 部署
- [ ] 已创建 COS 存储桶
- [ ] 已开启静态网站托管
- [ ] 已设置默认首页：`index.html`
- [ ] 已设置错误页面：`index.html`
- [ ] 已上传 `dist` 目录所有文件到 COS
- [ ] 已记录 COS 访问域名
- [ ] （可选）已配置 CDN 加速

### 方案 B: CVM 部署
- [ ] 已创建 `/var/www/habit-tracker/frontend` 目录
- [ ] 已上传 `dist` 目录内容到服务器
- [ ] 已创建 `/etc/nginx/conf.d/habit-tracker-frontend.conf`
- [ ] 已配置 Vue Router history 模式支持
- [ ] 已执行 `nginx -t` 测试配置
- [ ] 已执行 `systemctl reload nginx`

### API 地址配置
- [ ] 前端 API 地址已更新为生产环境地址
- [ ] 如果使用 HTTPS，API 地址已使用 `https://`
- [ ] 已重新构建前端（如果修改了 API 地址）

---

## 🌐 域名和 HTTPS

### 域名配置
- [ ] 已添加 A 记录指向服务器 IP
- [ ] 已添加 www 子域名（可选）
- [ ] 已添加 api 子域名（如果 API 使用独立域名）
- [ ] DNS 解析已生效（使用 `ping` 或 `nslookup` 测试）

### SSL 证书
- [ ] 已申请 SSL 证书
- [ ] 已下载证书文件（Nginx 版本）
- [ ] 已上传证书到 `/etc/nginx/ssl/`
- [ ] Nginx 配置已添加 SSL 配置
- [ ] 已配置 HTTP 到 HTTPS 重定向
- [ ] 已执行 `nginx -t` 测试配置
- [ ] 已执行 `systemctl reload nginx`
- [ ] 已测试 HTTPS 访问正常

---

## 🔥 防火墙配置

- [ ] 已开放 80 端口（HTTP）
- [ ] 已开放 443 端口（HTTPS）
- [ ] 已开放 22 端口（SSH，如果使用）
- [ ] 已测试防火墙规则生效

---

## ✅ 最终验证

### 功能测试
- [ ] 前端页面可以正常访问
- [ ] 可以正常注册账号
- [ ] 可以正常登录
- [ ] 可以添加习惯
- [ ] 可以打卡
- [ ] 可以查看统计信息
- [ ] 可以查看热力图

### 性能测试
- [ ] 页面加载速度正常
- [ ] API 响应速度正常
- [ ] 移动端访问正常

### 安全检查
- [ ] `.env` 文件权限已设置（600）
- [ ] 敏感文件已禁止访问（Nginx 配置）
- [ ] 数据库密码强度足够
- [ ] SSH 密钥认证已配置（推荐）

---

## 📝 部署后维护

### 备份配置
- [ ] 已设置数据库自动备份（如果使用云数据库）
- [ ] 已设置代码备份计划
- [ ] 已记录所有配置信息（密码、密钥等）

### 监控配置
- [ ] 已配置日志轮转
- [ ] 已设置日志监控（可选）
- [ ] 已配置服务器监控（可选）

### 文档记录
- [ ] 已记录服务器 IP 和登录信息
- [ ] 已记录数据库连接信息
- [ ] 已记录域名和证书信息
- [ ] 已记录所有密码和密钥（安全保存）

---

## 🎉 部署完成！

所有项目都打勾后，你的应用就已经成功部署了！

**访问地址：**
- 前端：`https://yourdomain.com`
- API：`https://api.yourdomain.com`

**下一步：**
1. 注册第一个账号
2. 添加你的第一个习惯
3. 开始追踪你的习惯！

---

## 🆘 遇到问题？

如果遇到问题，请：
1. 查看服务器日志：`/var/log/nginx/`
2. 查看 PHP 日志：`/var/log/php-fpm/`
3. 查看应用日志：`/var/www/habit-tracker/api/runtime/log/`
4. 参考 `DEPLOY-TENCENT-CLOUD.md` 中的常见问题部分
