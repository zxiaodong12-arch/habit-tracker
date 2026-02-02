# CloudBase 静态托管 404 错误修复指南

## 问题说明

访问 Vue Router 的路由（如 `/login`）时，CloudBase 静态托管返回 404 错误。

**错误信息：**
```
Request URL: https://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/login
Status Code: 404 Not Found
```

## 原因分析

Vue Router 使用 **history 模式**时，路由是客户端路由，不是真实的文件路径。当用户直接访问 `/login` 时：
1. 浏览器向服务器请求 `/login` 文件
2. CloudBase 找不到 `login` 文件，返回 404
3. 实际上应该返回 `index.html`，让 Vue Router 处理路由

## 解决方案

### 方案一：使用 Hash 模式（最简单，推荐）

如果找不到 CloudBase 控制台的设置选项，最简单的方法是改用 Hash 模式：

**修改路由配置：**

编辑 `vue-frontend/src/router/index.js`：

```javascript
import { createRouter, createWebHashHistory } from 'vue-router'  // 改为 createWebHashHistory
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
  history: createWebHashHistory(),  // 改为 Hash 模式
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

**优点：** 不需要服务器配置，立即生效  
**缺点：** URL 会变成 `https://域名/#/login`（有 `#` 符号）

### 方案二：在 CloudBase 控制台配置（如果找到设置）

1. **登录 CloudBase 控制台**
   - 访问：https://console.cloud.tencent.com/tcb

2. **进入静态网站托管**
   - 选择你的环境（如：lego-87）
   - 左侧菜单找到 **静态网站托管** 或 **Website Hosting**

3. **查找配置选项**
   - 在静态网站托管页面，查找以下位置：
     - **基础配置** → **静态网站**
     - **设置** 或 **Settings**
     - **索引文档配置** 或 **Index Document**
     - **错误文档配置** 或 **Error Document**

4. **配置错误页面**
   - 找到 **错误文档** 或 **Error Document** 配置项
   - 设置为：`index.html`
   - 点击 **保存**

5. **配置索引页面**
   - **索引文档**（默认首页）：`index.html`
   - 点击 **保存**

6. **验证**
   - 等待 2-5 分钟让配置生效
   - 访问 `https://你的域名/login`
   - 应该正常显示登录页面

**如果找不到这些选项：**
- 可能是 CloudBase 版本不同，界面有变化
- 建议使用方案一（Hash 模式）或方案三（CLI 配置）

### 方案三：使用 CloudBase CLI 配置

如果你使用 CloudBase CLI 部署：

1. **在项目根目录创建 `cloudbase.json`**：

```json
{
  "version": "2.0",
  "hosting": {
    "static": {
      "index": "index.html",
      "error": "index.html",
      "rewrites": [
        {
          "source": "**",
          "destination": "/index.html"
        }
      ]
    }
  }
}
```

2. **使用 CLI 部署**：
```bash
# 安装 CloudBase CLI（如果未安装）
npm install -g @cloudbase/cli

# 登录
tcb login

# 部署
tcb hosting deploy vue-frontend/dist
```

### 方案四：手动创建重定向文件

如果 CloudBase 支持 `_redirects` 文件（类似 Netlify），可以：

1. **确保 `_redirects` 文件在 dist 目录**
   - 文件已在 `vue-frontend/public/_redirects`
   - 构建时会自动复制到 `dist` 目录

2. **重新构建和部署**：
```bash
cd vue-frontend
npm run build
# 重新上传 dist 目录到 CloudBase
```

### 方案五：检查 CloudBase 控制台的具体位置

如果找不到设置选项，尝试以下路径：

1. **方法 A：通过环境入口**
   - CloudBase 控制台 → 选择环境 → **静态网站托管** → 点击进入
   - 在页面顶部或右侧查找 **设置**、**配置**、**Settings** 按钮

2. **方法 B：通过文件管理**
   - CloudBase 控制台 → 静态网站托管 → **文件管理**
   - 查找页面上的 **配置** 或 **设置** 选项

3. **方法 C：直接访问配置 URL**
   - 尝试访问：`https://console.cloud.tencent.com/tcb/hosting/setting`
   - 或：`https://console.cloud.tencent.com/tcb/env/[环境ID]/hosting/setting`

4. **方法 D：查看帮助文档**
   - 在控制台页面查找 **帮助** 或 **文档** 链接
   - 搜索 "静态网站托管配置" 或 "error document"

## 详细配置步骤（CloudBase 控制台）

### 步骤 1: 进入静态网站托管设置

1. 登录腾讯云控制台
2. 进入 **云开发 CloudBase**
3. 选择你的环境（如：lego-87）
4. 左侧菜单 → **静态网站托管** → **设置**

### 步骤 2: 配置错误页面

在设置页面找到：
- **索引文档（默认首页）**: `index.html`
- **错误文档（404 页面）**: `index.html` ← **关键配置**

**说明：**
- **索引文档**：访问根路径时返回的文件
- **错误文档**：访问不存在的路径时返回的文件（设置为 `index.html` 后，所有路由都会返回 `index.html`，由 Vue Router 处理）

### 步骤 3: 保存配置

点击**保存**按钮，配置立即生效（可能需要等待几分钟）。

### 步骤 4: 验证

```bash
# 测试根路径
curl https://你的域名/

# 测试路由（应该返回 200，而不是 404）
curl -I https://你的域名/login

# 查看返回内容（应该包含 Vue 应用的 HTML）
curl https://你的域名/login | head -20
```

## 验证配置是否生效

### 方法 1: 浏览器测试

1. 访问 `https://你的域名/login`
2. 应该显示登录页面，而不是 404
3. 检查浏览器开发者工具（F12）→ Network 面板：
   - `/login` 请求应该返回 200
   - 返回的内容应该是 `index.html`

### 方法 2: 使用 curl

```bash
# 测试根路径
curl -I https://你的域名/

# 测试路由（应该返回 200，而不是 404）
curl -I https://你的域名/login

# 查看返回内容（应该包含 Vue 应用的 HTML）
curl https://你的域名/login | head -20
```

## 常见问题

### Q: 配置后还是 404？

**A:** 可能原因：
1. 配置未保存或未生效（等待几分钟）
2. 缓存问题（清除浏览器缓存）
3. CDN 缓存（如果使用 CDN，需要刷新缓存）

**解决：**
- 确认配置已保存
- 等待 2-5 分钟让配置生效
- 清除浏览器缓存：`Ctrl + Shift + R` 或 `Cmd + Shift + R`
- 使用无痕模式测试

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

- [ ] CloudBase 静态网站托管已开启
- [ ] 索引文档设置为 `index.html`
- [ ] **错误文档设置为 `index.html`**（关键）
- [ ] 配置已保存
- [ ] 已等待配置生效（2-5 分钟）
- [ ] 已清除浏览器缓存
- [ ] 已测试访问 `/login` 路由
- [ ] Vue Router 使用 `createWebHistory()`（History 模式）

## 快速修复步骤总结

1. **登录 CloudBase 控制台**
   - https://console.cloud.tencent.com/tcb

2. **进入静态网站托管设置**
   - 环境 → 静态网站托管 → 设置

3. **配置错误页面**
   - 错误文档：`index.html`
   - 索引文档：`index.html`
   - 保存

4. **等待生效**
   - 等待 2-5 分钟

5. **测试**
   - 访问 `https://你的域名/login`
   - 应该正常显示登录页面

## 总结

**核心解决方案：**
在 CloudBase 静态网站托管设置中，将**错误文档**设置为 `index.html`，这样所有不存在的路径都会返回 `index.html`，由 Vue Router 处理路由。

**配置位置：**
CloudBase 控制台 → 环境 → 静态网站托管 → 设置 → 错误文档
