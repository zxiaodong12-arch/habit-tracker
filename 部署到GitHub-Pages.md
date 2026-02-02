# 使用 GitHub Pages 托管静态网站

本指南将帮助你使用 GitHub Pages 托管 Vue 前端应用。

## 方法一：使用 GitHub Actions 自动部署（推荐）

这是最简单和自动化的方式，每次推送到主分支时自动构建并部署。

### 步骤 1：准备 GitHub 仓库

1. 在 GitHub 上创建一个新仓库（如果还没有）
2. 将代码推送到仓库

```bash
cd /System/Volumes/Data/data/RD/habit-tracker
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/你的用户名/你的仓库名.git
git push -u origin main
```

### 步骤 2：配置 GitHub Actions

项目已经包含了 GitHub Actions 工作流文件（`.github/workflows/deploy.yml`），它会自动：
- 监听 `main` 分支的推送
- 构建 Vue 前端
- 部署到 GitHub Pages

### 步骤 3：启用 GitHub Pages

1. 进入 GitHub 仓库页面
2. 点击 **Settings**（设置）
3. 在左侧菜单找到 **Pages**（页面）
4. 在 **Source**（源）部分：
   - 选择 **GitHub Actions** 作为源
   - 或者选择 **Deploy from a branch**，然后选择 `gh-pages` 分支和 `/ (root)` 目录

### 步骤 4：配置环境变量（如果需要）

如果你的 API 地址需要配置，可以：

1. 在仓库 Settings → Secrets and variables → Actions
2. 添加 `VITE_API_BASE_URL` 环境变量（可选）
3. 或者直接修改 `.github/workflows/deploy.yml` 中的环境变量

### 步骤 5：访问网站

部署完成后，你的网站将在以下地址可用：
- 如果仓库名是 `username.github.io`：`https://你的用户名.github.io`
- 如果仓库名是其他名称：`https://你的用户名.github.io/仓库名`

## 方法二：手动部署

如果你想手动控制部署过程：

### 步骤 1：构建项目

```bash
cd vue-frontend
npm install
npm run build
```

### 步骤 2：创建 gh-pages 分支

```bash
# 切换到 dist 目录（构建输出目录）
cd ../dist

# 初始化 git（如果还没有）
git init
git checkout -b gh-pages
git add .
git commit -m "Deploy to GitHub Pages"

# 添加远程仓库
git remote add origin https://github.com/你的用户名/你的仓库名.git

# 推送到 gh-pages 分支
git push -u origin gh-pages
```

### 步骤 3：启用 GitHub Pages

1. 进入 GitHub 仓库 Settings → Pages
2. 选择 **Deploy from a branch**
3. 选择 `gh-pages` 分支和 `/ (root)` 目录
4. 点击 **Save**

### 步骤 4：更新部署

每次需要更新时：

```bash
cd vue-frontend
npm run build
cd ../dist
git add .
git commit -m "Update deployment"
git push
```

## 配置说明

### Base 路径配置

如果你的仓库名不是 `username.github.io`，网站会部署在子路径下（如 `/仓库名`）。

在这种情况下，需要修改 `vue-frontend/vite.config.js`：

```javascript
export default defineConfig({
  base: '/你的仓库名/',  // 改为你的仓库名
  // ... 其他配置
})
```

### API 地址配置

如果后端 API 地址需要配置，可以：

1. **使用环境变量**（推荐）：
   - 创建 `.env.production` 文件：
     ```
     VITE_API_BASE_URL=https://your-api-domain.com/api
     ```
   - 或者在 GitHub Actions 工作流中配置环境变量

2. **直接修改代码**：
   - 编辑 `vue-frontend/src/services/api.js`
   - 修改 `baseURL` 的默认值

### 自定义域名

如果你想使用自定义域名：

1. 在仓库根目录创建 `CNAME` 文件，内容为你的域名：
   ```
   yourdomain.com
   ```

2. 在 GitHub Pages 设置中配置自定义域名

3. 在你的域名 DNS 设置中添加 CNAME 记录指向 `你的用户名.github.io`

## 常见问题

### Q: 部署后页面显示 404？

**A:** 检查以下几点：
1. 确认 `vite.config.js` 中的 `base` 路径配置正确
2. 如果使用子路径，确保 base 设置为 `/仓库名/`
3. 检查 GitHub Pages 设置中的源分支和目录是否正确

### Q: 如何更新部署？

**A:** 
- 如果使用 GitHub Actions：直接推送到 `main` 分支即可自动部署
- 如果手动部署：重新构建并推送到 `gh-pages` 分支

### Q: 构建失败怎么办？

**A:** 
1. 检查 GitHub Actions 日志查看错误信息
2. 确保 `package.json` 中的依赖都正确安装
3. 检查 Node.js 版本是否兼容（推荐 18+）

### Q: 如何回退到之前的版本？

**A:** 
1. 在 GitHub Actions 中查看历史部署记录
2. 或者手动切换到之前的 commit 并重新部署

### Q: API 请求跨域问题？

**A:** 
1. 确保后端 API 支持 CORS
2. 检查 API 地址是否正确配置
3. 如果 API 在 HTTPS，确保前端也在 HTTPS（GitHub Pages 默认 HTTPS）

## 最佳实践

1. **使用 GitHub Actions**：自动化部署，减少手动操作
2. **环境变量管理**：使用 GitHub Secrets 存储敏感信息
3. **分支保护**：保护 `main` 分支，使用 Pull Request 进行代码审查
4. **定期更新**：保持依赖包和 Node.js 版本更新
5. **监控部署**：关注 GitHub Actions 的部署状态

## 相关资源

- [GitHub Pages 官方文档](https://docs.github.com/en/pages)
- [GitHub Actions 文档](https://docs.github.com/en/actions)
- [Vite 部署指南](https://vitejs.dev/guide/static-deploy.html)
