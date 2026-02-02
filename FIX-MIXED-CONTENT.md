# 混合内容（Mixed Content）错误修复指南

## 问题说明

错误信息：`(blocked:mixed-content)` - 混合内容被阻止

**原因：**
- 前端部署在 HTTPS 的 CloudBase 上
- API 请求使用 HTTP：`http://1.15.12.78/api`
- 浏览器安全策略阻止从 HTTPS 页面加载 HTTP 资源

## 解决方案

### 方案一：API 服务器配置 HTTPS（推荐）

如果 API 服务器支持 HTTPS，配置并使用 HTTPS：

1. **在 API 服务器上配置 SSL 证书**
   - 使用 Let's Encrypt 免费证书
   - 或使用腾讯云 SSL 证书

2. **修改前端 API 地址**
   
   创建 `vue-frontend/.env.production`：
   ```env
   VITE_API_BASE_URL=https://api.yourdomain.com/api
   ```
   
   或直接修改 `src/services/api.js`：
   ```javascript
   const baseURL = import.meta.env.VITE_API_BASE_URL || 'https://1.15.12.78/api'
   ```

3. **重新构建和部署**
   ```bash
   cd vue-frontend
   npm run build
   ```

### 方案二：自动协议选择（已实现）

代码已修改为根据当前页面协议自动选择 HTTP/HTTPS：

- 如果页面是 HTTPS，API 使用 HTTPS
- 如果页面是 HTTP，API 使用 HTTP

**注意：** 这要求 API 服务器同时支持 HTTP 和 HTTPS。

### 方案三：使用环境变量配置

1. **创建生产环境配置文件**

   创建 `vue-frontend/.env.production`：
   ```env
   VITE_API_BASE_URL=https://你的API域名/api
   ```

2. **重新构建**
   ```bash
   cd vue-frontend
   npm run build
   ```

3. **部署**

   将 `dist` 目录重新上传到 CloudBase。

## 详细配置步骤

### 步骤 1: 配置 API 服务器 HTTPS

如果使用 Nginx：

```nginx
server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;  # 或使用 IP
    
    ssl_certificate /path/to/cert.crt;
    ssl_certificate_key /path/to/key.key;
    
    # ... 其他配置
}
```

### 步骤 2: 修改前端配置

**方法 A：使用环境变量（推荐）**

创建 `vue-frontend/.env.production`：
```env
VITE_API_BASE_URL=https://1.15.12.78/api
```

**方法 B：直接修改代码**

编辑 `vue-frontend/src/services/api.js`：
```javascript
const baseURL = import.meta.env.VITE_API_BASE_URL || 'https://1.15.12.78/api'
```

### 步骤 3: 重新构建和部署

```bash
cd vue-frontend
npm run build
# 重新上传 dist 目录到 CloudBase
```

## 验证

1. **检查浏览器控制台**
   - 不应该再有混合内容错误
   - API 请求应该成功

2. **检查网络请求**
   - 打开浏览器开发者工具（F12）→ Network
   - API 请求应该使用 HTTPS
   - 状态码应该是 200

## 常见问题

### Q: API 服务器不支持 HTTPS 怎么办？

**A:** 有几个选择：

1. **配置 API 服务器支持 HTTPS**（推荐）
   - 使用 Let's Encrypt 免费证书
   - 或使用腾讯云 SSL 证书

2. **使用反向代理**
   - 在支持 HTTPS 的服务器上配置反向代理
   - 将 HTTPS 请求转发到 HTTP 的 API 服务器

3. **使用 CloudBase 云函数**
   - 将 API 迁移到 CloudBase 云函数
   - CloudBase 自动提供 HTTPS

### Q: 如何获取免费 SSL 证书？

**A:** 使用 Let's Encrypt：

```bash
# 安装 certbot
sudo apt install certbot python3-certbot-nginx  # Ubuntu
# 或
sudo yum install certbot python3-certbot-nginx  # CentOS

# 获取证书
sudo certbot --nginx -d yourdomain.com
```

### Q: 开发环境也需要 HTTPS 吗？

**A:** 不需要。代码已自动处理：
- 开发环境（localhost）使用 HTTP
- 生产环境（HTTPS）自动使用 HTTPS

## 快速修复

如果 API 服务器已支持 HTTPS，最快的方法是：

1. **创建 `.env.production` 文件**：
   ```bash
   cd vue-frontend
   echo "VITE_API_BASE_URL=https://1.15.12.78/api" > .env.production
   ```

2. **重新构建**：
   ```bash
   npm run build
   ```

3. **重新部署**：
   将 `dist` 目录重新上传到 CloudBase

## 总结

**核心问题：** HTTPS 页面不能请求 HTTP 资源

**解决方案：**
1. API 服务器配置 HTTPS（推荐）
2. 前端使用 HTTPS API 地址
3. 重新构建和部署

**配置位置：**
- `vue-frontend/.env.production` - 生产环境变量
- `vue-frontend/src/services/api.js` - API 配置
