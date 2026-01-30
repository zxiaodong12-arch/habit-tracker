# 用户注册和登录功能

## ✅ 已实现

### 后端 API

1. **POST /api/auth/register** - 用户注册
2. **POST /api/auth/login** - 用户登录
3. **GET /api/auth/me** - 获取当前用户信息（需要 token）
4. **POST /api/auth/logout** - 登出

### 前端功能

1. **登录/注册界面** - 模态框形式，可在登录和注册之间切换
2. **Token 自动管理** - 登录后自动保存 token，所有请求自动携带
3. **用户信息显示** - 顶部显示当前登录用户
4. **自动登出** - Token 过期或无效时自动清除登录状态

## 🚀 使用流程

### 1. 首次使用

1. 打开页面，自动显示登录界面
2. 点击"切换到注册"
3. 填写用户名、密码（邮箱可选）
4. 点击"注册"
5. 注册成功后，切换到登录模式
6. 输入用户名和密码登录
7. 登录成功后，自动进入主应用

### 2. 已登录用户

- 打开页面，自动检查 localStorage 中的 token
- 如果 token 有效，直接进入主应用
- 如果 token 无效，显示登录界面

### 3. 登出

- 点击右上角"登出"按钮
- 确认后清除登录状态，返回登录界面

## 📋 API 测试

### 注册

```bash
curl -X POST http://habit-tracker.com:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"123456","email":"test@example.com"}'
```

### 登录

```bash
curl -X POST http://habit-tracker.com:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"123456"}'
```

返回：
```json
{
  "success": true,
  "data": {
    "token": "MToxNzY5NzU3MjEwOjU3NmQzNzhmOTZjOGViZThjOGYyMzhkN2Q3ODAzYzM4",
    "user": {
      "id": 1,
      "username": "testuser",
      "email": "test@example.com"
    }
  }
}
```

### 使用 Token 访问 API

```bash
curl http://habit-tracker.com:8080/api/habits \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🔒 安全说明

### Token 机制

- Token 有效期：7 天
- Token 格式：`base64(userId:timestamp:hash)`
- 自动验证：每次请求都会验证 token 有效性

### 密码安全

- 使用 `password_hash()` 加密存储
- 密码不会在 API 响应中返回

### 生产环境建议

1. **使用 JWT** - 替换当前的简单 token 机制
2. **HTTPS** - 必须使用 HTTPS 传输
3. **Token 刷新** - 实现 refresh token 机制
4. **密码策略** - 添加最小长度、复杂度要求

## 📝 数据流程

1. **注册** → 创建用户 → 返回用户信息
2. **登录** → 验证密码 → 生成 token → 返回 token 和用户信息
3. **API 请求** → 携带 token → 验证 token → 返回数据
4. **登出** → 清除 token → 返回登录界面

## ⚠️ 注意事项

1. **兼容模式** - 如果未登录，API 会回退到使用 `user_id` 参数（兼容旧代码）
2. **Token 存储** - Token 存储在 localStorage，刷新页面不会丢失
3. **自动登出** - 401 错误会自动清除登录状态
4. **用户隔离** - 登录后只能看到和操作自己的习惯数据
