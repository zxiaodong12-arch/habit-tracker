# API 地址配置指南

## 📍 什么是 API 地址？

API 地址是**后端 ThinkPHP API 的访问地址**，前端需要通过这个地址来调用后端接口（登录、注册、获取习惯数据等）。

## 🔍 如何确定你的 API 地址？

### 情况 1: 使用域名（推荐）

如果你有域名并已配置 DNS 解析：

```
https://api.yourdomain.com/api
```

**配置步骤：**
1. 在域名服务商处添加 A 记录：
   - 主机记录：`api`
   - 记录值：你的服务器 IP
2. 在服务器上配置 Nginx，将 `api.yourdomain.com` 指向 API 目录
3. 配置 SSL 证书（推荐使用 HTTPS）

**示例：**
- 域名：`example.com`
- API 地址：`https://api.example.com/api`

---

### 情况 2: 使用服务器 IP 地址

如果你没有域名，可以直接使用服务器 IP：

```
http://你的服务器IP:端口/api
```

**常见端口：**
- `80` - HTTP（默认）
- `8080` - 自定义端口（避免冲突）
- `443` - HTTPS（默认）

**示例：**
- 服务器 IP：`123.456.789.0`
- 端口：`8080`
- API 地址：`http://123.456.789.0:8080/api`

**注意：**
- 如果使用 IP 地址，建议使用非标准端口（如 8080）避免与其他服务冲突
- 确保服务器防火墙已开放对应端口

---

### 情况 3: 本地开发测试

本地开发时使用：

```
http://localhost:8080/api
```

或

```
http://127.0.0.1:8080/api
```

---

## 🎯 部署脚本使用示例

### 使用域名

```bash
./deploy.sh prod https://api.example.com/api
```

### 使用 IP 地址

```bash
./deploy.sh prod http://123.456.789.0:8080/api
```

### 本地测试

```bash
./deploy.sh prod http://localhost:8080/api
```

---

## ✅ 如何验证 API 地址是否正确？

### 方法 1: 使用 curl 测试

```bash
# 测试健康检查接口
curl http://你的API地址/health

# 应该返回类似：
# {"success":true,"message":"API is running"}
```

### 方法 2: 浏览器访问

在浏览器中访问：
```
http://你的API地址/health
```

应该看到 JSON 响应。

### 方法 3: 测试注册接口

```bash
curl -X POST http://你的API地址/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"123456"}'
```

---

## 🔧 常见配置场景

### 场景 1: 前端和后端在同一台服务器

**配置：**
- 前端：`https://www.example.com`（端口 443）
- 后端：`https://api.example.com`（端口 443）

**Nginx 配置：**
```nginx
# 前端
server {
    listen 443 ssl;
    server_name www.example.com;
    root /var/www/habit-tracker/frontend;
    # ...
}

# 后端 API
server {
    listen 443 ssl;
    server_name api.example.com;
    root /var/www/habit-tracker/api/public;
    # ...
}
```

**部署命令：**
```bash
./deploy.sh prod https://api.example.com/api
```

---

### 场景 2: 前端在 COS，后端在 CVM

**配置：**
- 前端：COS 静态网站托管（自动分配域名或自定义域名）
- 后端：`https://api.example.com` 或 `http://服务器IP:8080`

**部署命令：**
```bash
# 如果后端使用域名
./deploy.sh prod https://api.example.com/api

# 如果后端使用 IP
./deploy.sh prod http://123.456.789.0:8080/api
```

---

### 场景 3: 全本地开发

**配置：**
- 前端：`http://localhost:3000`
- 后端：`http://localhost:8080`

**部署命令：**
```bash
./deploy.sh dev
# 或
./deploy.sh prod http://localhost:8080/api
```

---

## ⚠️ 注意事项

### 1. CORS 跨域问题

如果前端和后端不在同一域名，需要配置 CORS：

**在 ThinkPHP 中添加中间件，或在 Nginx 配置中添加：**
```nginx
add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Methods 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
add_header Access-Control-Allow-Headers 'Authorization, Content-Type';
```

### 2. HTTPS vs HTTP

- **生产环境推荐使用 HTTPS**（更安全）
- 如果使用 HTTPS，API 地址必须以 `https://` 开头
- 如果使用 HTTP，API 地址必须以 `http://` 开头

### 3. 端口问题

- 确保服务器防火墙已开放对应端口
- 如果使用非标准端口（如 8080），需要在 API 地址中指定

### 4. 路径问题

- API 地址必须以 `/api` 结尾（这是 ThinkPHP 的路由前缀）
- 不要忘记最后的 `/api`

---

## 🆘 常见问题

### Q: 我不知道我的 API 地址是什么？

**A:** 按照以下步骤确定：

1. **如果你已经部署了后端：**
   - 查看 Nginx 配置文件中的 `server_name` 和 `listen` 端口
   - 或直接访问 `http://你的服务器IP:端口/api/health` 测试

2. **如果你还没有部署后端：**
   - 先按照 `DEPLOY-TENCENT-CLOUD.md` 部署后端
   - 部署完成后，根据你的配置确定 API 地址

### Q: 可以使用相对路径吗？

**A:** 如果前端和后端在同一域名下，可以使用相对路径：

```javascript
// 如果前端在 https://www.example.com
// 后端在 https://www.example.com/api
const baseURL = '/api'
```

但推荐使用完整 URL，更清晰明确。

### Q: 部署后如何修改 API 地址？

**A:** 需要重新构建前端：

1. 修改 `vue-frontend/src/services/api.js` 中的 `baseURL`
2. 或修改 `.env.production` 文件
3. 重新运行 `npm run build`
4. 重新上传 `dist` 目录

---

## 📝 总结

**API 地址 = 你的后端服务器访问地址 + `/api`**

**示例：**
- 服务器 IP：`123.456.789.0`，端口：`8080`
- API 地址：`http://123.456.789.0:8080/api`

- 域名：`api.example.com`
- API 地址：`https://api.example.com/api`

记住：**这个地址必须可以从用户浏览器访问到你的后端 API！**
