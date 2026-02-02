# API 测试命令

## 正确的登录请求

### 方法一：使用 JSON（推荐）

```bash
curl --location 'http://1.15.12.78/api/auth/login' \
--header 'Content-Type: application/json' \
--data '{
    "username": "legolas",
    "password": "021271"
}'
```

### 方法二：使用 form-urlencoded

```bash
curl --location 'http://1.15.12.78/api/auth/login' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'username=legolas' \
--data-urlencode 'password=021271'
```

## 错误的请求方式

❌ **不要使用 form-data**：
```bash
# 错误：使用 --form
curl --location 'http://1.15.12.78/auth/login' \
--form 'password="021271"' \
--form 'username="legolas"'
```

**问题：**
1. 路径错误：应该是 `/api/auth/login` 而不是 `/auth/login`
2. 格式错误：应该用 JSON 或 form-urlencoded，不是 form-data

## 其他 API 端点

### 注册

```bash
curl --location 'http://1.15.12.78/api/auth/register' \
--header 'Content-Type: application/json' \
--data '{
    "username": "testuser",
    "password": "123456",
    "email": "test@example.com"
}'
```

### 获取当前用户（需要 token）

```bash
curl --location 'http://1.15.12.78/api/auth/me' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

### 登出

```bash
curl --location --request POST 'http://1.15.12.78/api/auth/logout' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

## 测试步骤

1. **先测试健康检查**
   ```bash
   curl http://1.15.12.78/api/health
   ```

2. **测试登录**
   ```bash
   curl --location 'http://1.15.12.78/api/auth/login' \
   --header 'Content-Type: application/json' \
   --data '{"username":"legolas","password":"021271"}'
   ```

3. **查看响应**
   - 成功应该返回 JSON，包含 `token` 和 `user` 信息
   - 失败会返回错误信息

## 常见错误

### 错误 1: "非法请求:auth/login"

**原因：** 路由未配置或路径错误

**解决：** 
- 确保路径是 `/api/auth/login`（有 `/api` 前缀）
- 确保路由配置文件已更新

### 错误 2: "用户名和密码不能为空"

**原因：** 请求格式错误，数据未正确传递

**解决：** 
- 使用 JSON 格式：`Content-Type: application/json`
- 或使用 form-urlencoded：`Content-Type: application/x-www-form-urlencoded`
- 不要使用 form-data

### 错误 3: 404 Not Found

**原因：** 路径错误或路由未配置

**解决：** 
- 检查路径是否包含 `/api` 前缀
- 确保路由配置文件已更新并重启服务

## 重启服务（如果修改了路由）

如果修改了路由配置，需要重启 PHP-FPM 和 Nginx：

```bash
# 重启 PHP-FPM
sudo systemctl restart php-fpm  # CentOS
# 或
sudo systemctl restart php8.0-fpm  # Ubuntu

# 重启 Nginx
sudo systemctl restart nginx
```
