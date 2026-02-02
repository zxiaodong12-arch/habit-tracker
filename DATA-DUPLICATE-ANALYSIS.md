# 数据重复问题分析

## 问题原因

数据重复主要有以下几个可能的原因：

### 1. **双重加载问题** ⚠️ 主要原因

**问题流程：**
```
登录时：
1. api.login() 触发 'auth:login' 事件 → 调用 loadApp() → 加载数据
2. 登录成功后，又直接调用 loadApp() → 再次加载数据
```

**代码位置：**
- `api-service.js:108` - `api.login()` 会触发 `auth:login` 事件
- `app.js:31-37` - `init()` 中监听 `auth:login` 事件，会调用 `loadApp()`
- `app.js:169` - 登录成功后，又直接调用 `loadApp()`

**结果：** 数据被加载两次，导致重复显示

### 2. **事件监听器重复注册**

**问题：**
- 每次 `init()` 都会添加新的 `auth:login` 事件监听器
- 旧的监听器没有被移除
- 如果页面刷新多次，会有多个监听器同时响应

**代码位置：**
- `app.js:31` - `window.addEventListener('auth:login', ...)` 每次 init 都会执行

**结果：** 一个事件可能触发多次 `loadApp()`

### 3. **API 返回重复数据**（可能性较小）

**检查方法：**
- 查看浏览器 Network 面板，检查 `/api/habits` 返回的数据
- 检查后端数据库是否有重复记录

### 4. **渲染时重复添加**

**问题：**
- `renderHabits()` 和 `renderArchivedHabits()` 可能都渲染了相同的习惯
- 如果 `habits` 数组中有重复项，会导致重复显示

## 解决方案

### 方案 1：移除 api.login() 中的事件触发（推荐）

在登录成功后，不触发 `auth:login` 事件，只直接调用 `loadApp()`：

```javascript
// api-service.js
async login(username, password) {
    const response = await this.request('/auth/login', {
        method: 'POST',
        body: JSON.stringify({
            username,
            password,
        }),
    });
    
    if (response.success && response.data.token) {
        this.setAuth(response.data.token, response.data.user);
        // 不触发事件，由调用方决定何时加载数据
        // window.dispatchEvent(new CustomEvent('auth:login', { detail: response.data.user }));
    }
    
    return response;
}
```

### 方案 2：使用事件标志防止重复加载

在 `loadApp()` 中添加加载标志：

```javascript
async loadApp() {
    if (this._loading) {
        console.log('数据正在加载中，跳过重复加载');
        return;
    }
    this._loading = true;
    try {
        await this.loadHabits();
        // ...
    } finally {
        this._loading = false;
    }
}
```

### 方案 3：移除重复的事件监听器

使用 `removeEventListener` 或使用 `once: true` 选项：

```javascript
// 使用 once: true，只触发一次
window.addEventListener('auth:login', () => {
    this.showMainApp();
    this.loadApp();
}, { once: true });
```

### 方案 4：在数据加载时去重（已实现）

已经在 `loadHabits()` 中使用 Map 去重，但这是治标不治本的方法。

## 推荐修复方案

**最佳方案：** 方案 1 + 方案 2 组合
1. 移除 `api.login()` 中的事件触发
2. 在 `loadApp()` 中添加加载标志，防止并发加载
3. 登录成功后只调用一次 `loadApp()`
