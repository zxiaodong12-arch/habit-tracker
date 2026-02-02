# 简单部署指南 - 让项目能在腾讯云访问

## 当前状态

✅ **前端已部署**：CloudBase 静态托管  
✅ **后端 API**：服务器 `1.15.12.78`  
✅ **路由问题**：已用 Hash 模式解决（URL 有 `#`）

## 剩余问题

1. **混合内容错误**：HTTPS 页面不能请求 HTTP API
2. **刷新下载文件**：可能是浏览器缓存问题

## 最简单的解决方案

### 方案一：接受 HTTP API（最简单，适合测试）

如果只是想让项目能访问，可以暂时接受 HTTP API：

1. **修改前端 API 配置**

   编辑 `vue-frontend/src/services/api.js`，强制使用 HTTP：

   ```javascript
   // 强制使用 HTTP（仅用于测试）
   const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://1.15.12.78/api'
   ```

2. **重新构建和部署**

   ```bash
   cd vue-frontend
   npm run build
   # 重新上传 dist 目录到 CloudBase
   ```

3. **使用 HTTP 访问前端**

   如果 CloudBase 支持 HTTP，使用 HTTP 访问：
   - `http://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/#/login`

   **注意**：浏览器可能会显示"不安全"警告，但功能正常。

### 方案二：配置 API 服务器 HTTPS（推荐，但需要域名）

如果 API 服务器有域名：

1. **配置域名解析**
   - 将域名指向 `1.15.12.78`

2. **安装 SSL 证书**
   ```bash
   sudo yum install -y epel-release
   sudo yum install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d api.yourdomain.com
   ```

3. **修改前端配置**

   创建 `vue-frontend/.env.production`：
   ```env
   VITE_API_BASE_URL=https://api.yourdomain.com/api
   ```

4. **重新构建和部署**
   ```bash
   cd vue-frontend
   npm run build
   ```

### 方案三：使用 CloudBase 云函数（最简单，但需要改代码）

将 API 迁移到 CloudBase 云函数，自动支持 HTTPS：

1. **创建云函数**
2. **部署 API 代码到云函数**
3. **前端调用云函数**

## 快速检查清单

### 前端部署
- [ ] 前端已构建（`npm run build`）
- [ ] `dist` 目录已上传到 CloudBase
- [ ] 可以访问：`http://域名/#/login`

### 后端 API
- [ ] API 服务器运行正常
- [ ] 可以访问：`http://1.15.12.78/api/health`
- [ ] API 返回正确的 JSON

### 混合内容问题
- [ ] 如果使用 HTTPS 前端，API 必须是 HTTPS
- [ ] 或使用 HTTP 前端访问（浏览器会显示警告）

## 最简单的部署步骤（5 分钟）

### 1. 确保后端 API 正常

```bash
# 在服务器上测试
curl http://1.15.12.78/api/health
```

### 2. 构建前端

```bash
cd vue-frontend
npm run build
```

### 3. 上传到 CloudBase

- CloudBase 控制台 → 静态网站托管 → 文件管理
- 上传 `dist` 目录下的所有文件

### 4. 测试访问

- 使用 HTTP 访问：`http://域名/#/login`
- 如果浏览器阻止混合内容，使用 HTTP 访问前端

## 如果遇到问题

### 问题 1：混合内容错误

**解决**：使用 HTTP 访问前端，或配置 API HTTPS

### 问题 2：刷新下载文件

**解决**：清除浏览器缓存（`Ctrl + Shift + Delete`）

### 问题 3：路由 404

**解决**：已用 Hash 模式解决，URL 有 `#` 符号

## 总结

**最简单的方案**：
1. 前端用 Hash 模式（已配置）
2. 如果 API 不支持 HTTPS，前端也用 HTTP 访问
3. 清除浏览器缓存
4. 完成！

**生产环境推荐**：
- 配置 API 服务器 HTTPS（需要域名）
- 或使用 CloudBase 云函数
