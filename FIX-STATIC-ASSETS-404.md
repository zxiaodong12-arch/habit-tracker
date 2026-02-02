# 静态资源 404 错误修复指南

## 问题说明

访问静态网站托管后，JavaScript/CSS 文件返回 404 错误。

**错误示例：**
```
Request URL: https://lego-87-3galps7s9490f422-1399785706.tcloudbaseapp.com/assets/index-D83JYL9H.js
Status Code: 404 Not Found
```

## 原因分析

1. **文件未完整上传** - 只上传了 `index.html`，没有上传 `assets` 目录
2. **路径配置问题** - Vite 构建时使用了绝对路径，但静态网站托管需要相对路径
3. **上传路径错误** - 文件上传到了错误的目录层级

## 解决方案

### 方案一：检查文件上传（最常见）

确保上传了**所有文件**，不仅仅是 `index.html`：

```
dist/
├── index.html          ✅ 需要上传
├── assets/             ✅ 需要上传整个目录
│   ├── index-*.js
│   ├── index-*.css
│   └── ...
├── icon.svg            ✅ 需要上传
└── manifest.json       ✅ 需要上传
```

**上传步骤：**
1. 进入 `dist` 目录
2. 选择**所有文件和文件夹**
3. 上传到 COS 存储桶的**根目录**（不是子目录）

### 方案二：修复 Vite 配置（使用相对路径）

如果文件已上传但仍 404，可能是路径问题。修改 Vite 配置使用相对路径：

```javascript
// vite.config.js
export default defineConfig({
  plugins: [vue()],
  base: './',  // 添加这行，使用相对路径
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    outDir: '../dist',
    emptyOutDir: true
  }
})
```

然后**重新构建**：

```bash
cd vue-frontend
npm run build
```

### 方案三：检查 COS 静态网站托管配置

1. **默认首页**：设置为 `index.html`
2. **错误页面**：也设置为 `index.html`（用于 Vue Router）
3. **访问路径**：确保访问的是根路径，不是子目录

### 方案四：检查文件权限

在 COS 控制台检查文件权限：
- 确保所有文件都是**公共读**权限
- 或使用**私有读 + CDN** 方式

## 快速修复步骤

### 1. 重新构建（使用相对路径）

```bash
cd vue-frontend

# 修改 vite.config.js，添加 base: './'
# 然后重新构建
npm run build
```

### 2. 检查构建输出

```bash
# 查看 dist 目录结构
ls -la dist/
ls -la dist/assets/

# 查看 index.html 中的路径
cat dist/index.html | grep -E "(src|href)="
```

应该看到相对路径：
```html
<script type="module" src="./assets/index-xxx.js"></script>
```

而不是绝对路径：
```html
<script type="module" src="/assets/index-xxx.js"></script>
```

### 3. 重新上传所有文件

```bash
# 方法1: 使用 COS 控制台
# 1. 进入 COS 控制台
# 2. 选择存储桶
# 3. 上传文件夹，选择 dist 目录
# 4. 确保上传到根目录

# 方法2: 使用 COS CLI
cd dist
coscmd upload -rs . /
```

### 4. 验证上传

在 COS 控制台检查：
- ✅ `index.html` 存在
- ✅ `assets/` 目录存在
- ✅ `assets/` 目录下有所有 JS/CSS 文件
- ✅ `icon.svg` 存在
- ✅ `manifest.json` 存在

### 5. 清除浏览器缓存

```
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

## 完整修复流程

```bash
# 1. 修改 Vite 配置
cd vue-frontend
# 编辑 vite.config.js，添加 base: './'

# 2. 重新构建
npm run build

# 3. 检查构建输出
ls -la dist/
cat dist/index.html

# 4. 上传到 COS（使用控制台或 CLI）
# 确保上传 dist 目录下的所有内容到根目录

# 5. 验证
# 访问网站，检查 Network 面板，确认资源加载成功
```

## 常见问题

### Q: 为什么文件上传了还是 404？

**A:** 可能原因：
1. 上传到了子目录，但访问路径不对
2. 文件权限问题
3. 浏览器缓存了旧的 404 响应

**解决：**
- 确保文件在根目录
- 检查文件权限
- 清除浏览器缓存

### Q: 如何确认文件已上传？

**A:** 
1. 在 COS 控制台查看文件列表
2. 直接访问文件 URL：`https://你的域名/assets/index-xxx.js`
3. 如果直接访问也 404，说明文件未上传或路径不对

### Q: 使用相对路径后路由不工作？

**A:** 确保静态网站托管的**错误页面**也设置为 `index.html`，这样 Vue Router 的 history 模式才能正常工作。

### Q: 如何批量上传文件？

**A:** 
```bash
# 使用 COS CLI
coscmd upload -rs dist/ /

# 或使用控制台
# 选择 dist 目录，上传整个文件夹
```

## 验证清单

- [ ] Vite 配置中添加了 `base: './'`
- [ ] 重新构建了项目
- [ ] 检查了 `dist/index.html` 中的路径是相对路径
- [ ] 上传了 `dist` 目录下的**所有文件**（包括 `assets` 目录）
- [ ] 文件上传到了 COS 存储桶的**根目录**
- [ ] 静态网站托管配置正确（默认首页、错误页面）
- [ ] 清除了浏览器缓存
- [ ] 直接访问资源文件 URL 可以访问
