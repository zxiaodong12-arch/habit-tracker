# 部署到 Netlify - 备选方案

## 为什么选择 Netlify？

- ✅ 免费
- ✅ 自动 HTTPS
- ✅ 支持 Vue Router History 模式
- ✅ 不会下载 HTML 文件
- ✅ 部署简单

## 部署步骤

### 方法一：拖拽部署（最简单）

1. **构建项目**
   ```bash
   cd vue-frontend
   npm run build
   ```

2. **访问 Netlify**
   - 打开：https://app.netlify.com
   - 注册/登录

3. **拖拽部署**
   - 将 `dist` 目录拖拽到 Netlify 页面
   - 等待部署完成
   - 获得一个 URL

### 方法二：通过 GitHub

1. **推送代码到 GitHub**

2. **在 Netlify 导入项目**
   - 点击 "Add new site" → "Import an existing project"
   - 选择 GitHub 仓库
   - 配置：
     - **Build command**: `cd vue-frontend && npm install && npm run build`
     - **Publish directory**: `vue-frontend/dist`

3. **部署**

## 配置文件

创建 `vue-frontend/public/_redirects`：

```
/*    /index.html   200
```

这个文件会在构建时自动复制到 `dist` 目录。

## 完成

部署完成后，访问 URL 即可，不会下载文件！
