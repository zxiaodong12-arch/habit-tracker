# 用户注册和登录功能说明

## ✅ 已实现的功能

### 后端 API

1. **用户注册** - `POST /api/auth/register`
   - 参数：`username`, `password`, `email`（可选）
   - 返回：用户信息（不含密码）

2. **用户登录** - `POST /api/auth/login`
   - 参数：`username`, `password`
   - 返回：`token` 和用户信息

3. **获取当前用户** - `GET /api/auth/me`
   - 需要 token 认证
   - 返回：当前登录用户信息

4. **登出** - `POST /api/auth/logout`
   - 清除认证信息

### 前端功能

1. **登录/注册模态框**
   - 可以在登录和注册模式之间切换
   - 表单验证

2. **Token 管理**
   - 自动保存 token 到 localStorage
   - 所有 API 请求自动携带 token
   - Token 过期自动清除

3. **用户信息显示**
   - 顶部显示当前登录用户
   - 登出按钮

## 🔧 使用说明

### 1. 注册新用户

1. 打开页面，会显示登录界面
2. 点击"切换到注册"
3. 填写用户名、密码、邮箱（可选）
4. 点击"注册"
5. 注册成功后，切换到登录模式登录

### 2. 登录

1. 在登录界面输入用户名和密码
2. 点击"登录"
3. 登录成功后，自动进入主应用

### 3. 登出

1. 点击右上角的"登出"按钮
2. 确认后清除登录状态

## 📋 API 接口

### 注册

```bash
curl -X POST http://habit-tracker.com:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "123456",
    "email": "test@example.com"
  }'
```

### 登录

```bash
curl -X POST http://habit-tracker.com:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "123456"
  }'
```

返回：
```json
{
  "success": true,
  "data": {
    "token": "base64_encoded_token",
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

### 当前实现

- 密码使用 `password_hash()` 加密存储
- Token 使用简单的 base64 编码（包含用户ID、时间戳、哈希）

### 生产环境建议

1. **使用 JWT Token**
   - 安装 `firebase/php-jwt` 或类似库
   - 实现更安全的 token 生成和验证

2. **Token 存储**
   - 使用 Redis 存储 token
   - 实现 token 刷新机制

3. **密码策略**
   - 最小长度要求
   - 复杂度要求
   - 密码重置功能

4. **HTTPS**
   - 生产环境必须使用 HTTPS

## 📝 数据库

确保用户表已创建（已在 `database.sql` 中定义）：

```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`)
);
```

## 🚀 测试

1. **注册测试用户**
   ```bash
   curl -X POST http://habit-tracker.com:8080/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{"username":"test","password":"123456"}'
   ```

2. **登录获取 token**
   ```bash
   curl -X POST http://habit-tracker.com:8080/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"username":"test","password":"123456"}'
   ```

3. **使用 token 访问 API**
   ```bash
   curl http://habit-tracker.com:8080/api/habits \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

## ⚠️ 注意事项

1. **Token 有效期** - 当前设置为 7 天
2. **兼容模式** - 如果未登录，API 会回退到使用 `user_id` 参数（兼容旧代码）
3. **自动登出** - Token 过期或无效时，前端会自动清除登录状态
