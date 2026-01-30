# API 集成说明

## ✅ 已完成的修改

1. **创建了 `api-service.js`** - API 服务层，封装所有 API 调用
2. **修改了 `app.js`** - 将所有 localStorage 操作改为 API 调用
3. **更新了 `index.html`** - 引入 `api-service.js`

## 🔧 配置 API 地址

在 `api-service.js` 中修改 API 基础地址：

```javascript
this.baseURL = 'http://habit-tracker.com:8080/api';
// 或
this.baseURL = 'http://localhost:8080/api';
```

## 📋 主要改动

### 1. 数据加载
- `loadHabits()` - 改为从 API 加载
- 自动加载每个习惯的打卡记录
- 如果 API 失败，会回退到 localStorage（兼容模式）

### 2. 习惯管理
- `addHabit()` - 调用 `POST /api/habits`
- `editHabit()` - 调用 `PUT /api/habits/:id`
- `deleteHabit()` - 调用 `DELETE /api/habits/:id`
- `toggleArchiveHabit()` - 调用 `PATCH /api/habits/:id/archive`

### 3. 打卡记录
- `toggleHabit()` - 调用 `POST /api/records/toggle`

### 4. 统计数据
- `updateStats()` - 调用 `GET /api/stats/user/:userId`

## 🚀 使用说明

### 1. 确保 API 服务运行

```bash
# 检查 API 是否可访问
curl http://habit-tracker.com:8080/api/health
```

### 2. 配置用户ID

在 `api-service.js` 中修改：

```javascript
this.userId = 1; // 改为你的用户ID
```

### 3. 测试

打开前端页面，所有操作都会调用 API。

## ⚠️ 注意事项

1. **CORS 配置** - 确保 API 允许前端域名的跨域请求
2. **错误处理** - API 失败时会显示错误提示
3. **兼容模式** - 如果 API 不可用，会尝试使用 localStorage
4. **数据格式** - API 返回的数据会自动转换为前端格式

## 🔄 数据迁移

如果之前使用 localStorage，可以使用迁移脚本：

```bash
# 导出 localStorage 数据
# 在浏览器控制台执行：
localStorage.getItem('habits')

# 保存为 JSON 文件，然后使用 API 迁移脚本
php api/scripts/migrate.php export.json 1
```

## 📝 待完善功能

- [ ] 用户登录/认证
- [ ] Token 管理
- [ ] 请求重试机制
- [ ] 离线缓存
- [ ] 数据同步冲突处理
