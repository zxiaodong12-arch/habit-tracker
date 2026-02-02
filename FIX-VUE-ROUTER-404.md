# Vue Router 404 错误修复指南

## 问题说明

访问 Vue Router 的路由（如 `/login`）时，COS 静态网站托管返回 404 错误。

**错误信息：**
```
404 Not Found
Code: NoSuchKey
Message: The specified key does not exist.
Key: login
```

## 原因分析

Vue Router 使用 **history 模式**时，路由是客户端路由，不是真实的文件路径。当用户直接访问 `/login` 时：
1. 浏览器向服务器请求 `/login` 文件
2. COS 找不到 `login` 文件，返回 404
3. 实际上应该返回 `index.html`，让 Vue Router 处理路由

## 解决方案

### 方案一：配置 COS 静态网站托管错误页面（推荐）

在腾讯云 COS 控制台配置：

1. **登录 COS 控制台**
   - 访问：https://console.cloud.tencent.com/cos

2. **选择存储桶**
   - 找到你的静态网站托管存储桶

3. **进入静态网站配置**
   - 左侧菜单 → **基础配置** → **静态网站**
   - 或直接访问：存储桶 → **基础配置** → **静态网站**

4. **配置错误页面**
   - **错误文档**：设置为 `index.html`
   - **索引文档**：设置为 `index.html`
   - 点击**保存**

5. **验证**
   - 访问 `https://你的域名/login`
   - 应该正常显示登录页面

### 方案二：使用 Hash 模式（备选方案）

如果方案一不工作，可以改用 Hash 模式（URL 会变成 `#/login`）：

修改 `vue-frontend/src/router/index.js`：

```javascript
import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router'

const router = createRouter({
  // 改为 Hash 模式
  history: createWebHashHistory(),
  routes: [
    // ... 路由配置
  ]
})
```

**缺点：** URL 会变成 `https://域名/#/login`，不够美观。

### 方案三：使用 CDN 配置（如果使用 CDN）

如果使用了腾讯云 CDN，可以在 CDN 配置中添加：

1. **进入 CDN 控制台**
2. **选择域名** → **高级配置** → **回源配置**
3. **配置回源规则**：
   - 当访问的文件不存在时，回源到 `index.html`

## 详细配置步骤（COS 静态网站托管）

### 步骤 1: 进入静态网站配置

1. 登录腾讯云控制台
2. 进入 **对象存储 COS**
3. 选择你的存储桶
4. 左侧菜单 → **基础配置** → **静态网站**

### 步骤 2: 配置索引文档和错误文档

```
索引文档（默认首页）: index.html
错误文档（404 页面）: index.html  ← 关键配置
```

**说明：**
- **索引文档**：访问根路径时返回的文件
- **错误文档**：访问不存在的路径时返回的文件（设置为 `index.html` 后，所有路由都会返回 `index.html`，由 Vue Router 处理）

### 步骤 3: 保存配置

点击**保存**按钮，配置立即生效。

### 步骤 4: 验证

```bash
# 测试根路径
curl https://你的域名/

# 测试路由
curl https://你的域名/login
curl https://你的域名/

# 都应该返回 index.html 的内容
```

## 验证配置是否生效

### 方法 1: 浏览器测试

1. 访问 `https://你的域名/login`
2. 应该显示登录页面，而不是 404

### 方法 2: 使用 curl

```bash
# 测试根路径
curl -I https://你的域名/

# 测试路由（应该返回 200，而不是 404）
curl -I https://你的域名/login

# 查看返回内容（应该包含 Vue 应用的 HTML）
curl https://你的域名/login | head -20
```

### 方法 3: 浏览器开发者工具

1. 打开浏览器开发者工具（F12）
2. 访问 `https://你的域名/login`
3. 查看 Network 面板：
   - `/login` 请求应该返回 200
   - 返回的内容应该是 `index.html`

## 常见问题

### Q: 配置后还是 404？

**A:** 可能原因：
1. 配置未保存
2. 缓存问题（清除浏览器缓存）
3. CDN 缓存（如果使用 CDN，需要刷新缓存）

**解决：**
- 确认配置已保存
- 清除浏览器缓存：`Ctrl + Shift + R`
- 如果使用 CDN，在 CDN 控制台刷新缓存

### Q: 配置错误页面后，所有 404 都返回 index.html？

**A:** 这是正常的。Vue Router 会处理路由，如果路由不存在，会在前端显示 404 页面（如果配置了的话）。

### Q: 如何在前端处理 404？

在 Vue Router 中添加 404 路由：

```javascript
{
  path: '/:pathMatch(.*)*',
  name: 'NotFound',
  component: () => import('@/views/NotFound.vue')
}
```

### Q: 使用 Hash 模式还是 History 模式？

**推荐 History 模式**（更美观）：
- URL 更简洁：`/login` vs `/#/login`
- 需要配置错误页面重定向

**Hash 模式**（更简单）：
- 不需要服务器配置
- URL 有 `#` 符号

## 完整配置检查清单

- [ ] COS 静态网站托管已开启
- [ ] 索引文档设置为 `index.html`
- [ ] **错误文档设置为 `index.html`**（关键）
- [ ] 配置已保存
- [ ] 已清除浏览器缓存
- [ ] 已测试访问 `/login` 路由
- [ ] Vue Router 使用 `createWebHistory()`（History 模式）

## 快速修复命令

如果使用腾讯云 CLI：

```bash
# 配置静态网站托管（需要安装 coscmd）
coscmd putbucketwebsite --index-document index.html --error-document index.html 存储桶名
```

## 总结

**核心解决方案：**
在 COS 静态网站托管中，将**错误文档**设置为 `index.html`，这样所有不存在的路径都会返回 `index.html`，由 Vue Router 处理路由。

**配置位置：**
COS 控制台 → 存储桶 → 基础配置 → 静态网站 → 错误文档
