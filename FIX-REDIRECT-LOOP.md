# CloudBase 重定向循环错误修复指南

## 问题说明

错误信息：`STATIC_RESOURCE_TOO_MANY_REDIRECTS` - 重定向次数过多

**原因：**
404 重定向规则配置过于宽泛，导致静态资源（CSS、JS、图片等）也被重定向到 `index.html`，形成无限循环。

## 解决方案

### 方案一：修改 CloudBase 重定向规则（推荐）

在 CloudBase 控制台修改重定向规则，**只对 HTML 路由生效，排除静态资源**：

1. **进入 CloudBase 控制台**
   - 静态网站托管 → 基础配置 → 路由配置

2. **修改或删除现有的 404 重定向规则**

3. **添加更精确的重定向规则**（如果支持条件匹配）：
   - **类型**：错误码
   - **描述**：404
   - **规则**：替换路径前缀（但需要添加条件）
   - **路径**：`index.html`
   - **条件**：只对不包含文件扩展名的路径生效

   或者使用更精确的规则：
   - **类型**：路径匹配
   - **规则**：匹配所有非静态资源的路径
   - **路径**：`index.html`

4. **如果 CloudBase 不支持条件匹配，删除 404 重定向规则**

### 方案二：使用 Hash 模式（最简单，推荐）

改用 Hash 模式，完全避免重定向问题：

**修改路由配置：**

编辑 `vue-frontend/src/router/index.js`：

```javascript
import { createRouter, createWebHashHistory } from 'vue-router'  // 改为 Hash 模式
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Login.vue'),
    meta: { requiresAuth: false }
  }
]

const router = createRouter({
  history: createWebHashHistory(), // 使用 Hash 模式
  routes
})

// ... 其他代码保持不变
```

然后重新构建和部署：
```bash
cd vue-frontend
npm run build
# 重新上传 dist 目录到 CloudBase
```

**优点：**
- 不需要服务器配置
- 完全避免重定向问题
- 立即生效

**缺点：**
- URL 会变成 `https://域名/#/login`（有 `#` 符号）

### 方案三：删除 404 重定向规则，使用其他方式

如果 CloudBase 不支持精确的重定向规则：

1. **删除现有的 404 重定向规则**
2. **使用 Hash 模式**（方案二）

## 详细修复步骤

### 步骤 1: 删除或修改重定向规则

1. 登录 CloudBase 控制台
2. 进入：静态网站托管 → 基础配置 → 路由配置
3. 找到 404 错误码的重定向规则
4. **删除该规则** 或 **修改为更精确的规则**

### 步骤 2: 改用 Hash 模式

1. 修改 `vue-frontend/src/router/index.js`
2. 将 `createWebHistory()` 改为 `createWebHashHistory()`
3. 重新构建和部署

### 步骤 3: 验证

1. 清除浏览器缓存
2. 访问网站，检查是否还有重定向错误
3. 检查静态资源（CSS、JS）是否正常加载

## 为什么会出现重定向循环？

**问题流程：**
1. 浏览器请求 `/assets/index.js`
2. CloudBase 找不到该文件，返回 404
3. 404 规则重定向到 `index.html`
4. `index.html` 中引用了 `/assets/index.js`
5. 再次请求 `/assets/index.js` → 404 → 重定向到 `index.html`
6. 形成无限循环

**解决方案：**
- 重定向规则应该只对 HTML 路由生效（如 `/login`、`/home`）
- 不应该对静态资源生效（如 `/assets/*.js`、`/*.css`）
- 如果 CloudBase 不支持精确规则，使用 Hash 模式

## 推荐方案

**强烈推荐使用 Hash 模式（方案二）**，因为：
1. 最简单，不需要复杂的服务器配置
2. 完全避免重定向问题
3. 立即生效
4. 对用户体验影响很小（只是 URL 多了 `#` 符号）

## 快速修复命令

```bash
# 1. 修改路由为 Hash 模式
# 编辑 vue-frontend/src/router/index.js
# 将 createWebHistory() 改为 createWebHashHistory()

# 2. 重新构建
cd vue-frontend
npm run build

# 3. 重新上传 dist 目录到 CloudBase
```

## 总结

**核心问题：** 404 重定向规则太宽泛，导致静态资源也被重定向

**最佳解决方案：** 使用 Hash 模式，完全避免重定向配置

**配置位置：**
- CloudBase 控制台 → 静态网站托管 → 基础配置 → 路由配置
- 删除或修改 404 重定向规则
