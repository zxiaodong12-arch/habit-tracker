# GitHub Pages 快速开始指南

## 🚀 最快部署方式（3 步）

### 1. 推送代码到 GitHub

```bash
cd /System/Volumes/Data/data/RD/habit-tracker
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/你的用户名/你的仓库名.git
git push -u origin main
```

### 2. 启用 GitHub Pages

1. 进入 GitHub 仓库 → **Settings** → **Pages**
2. 在 **Source** 选择 **GitHub Actions**
3. 保存设置

### 3. 等待自动部署

GitHub Actions 会自动构建并部署，几分钟后访问：
- `https://你的用户名.github.io/仓库名`

## 📝 配置 API 地址（可选）

如果需要配置后端 API 地址：

1. 进入仓库 **Settings** → **Secrets and variables** → **Actions**
2. 点击 **New repository secret**
3. 添加：
   - Name: `VITE_API_BASE_URL`
   - Value: `https://your-api-domain.com/api`
4. 保存后，下次推送代码会自动使用新的 API 地址

## 🔧 配置子路径（如果仓库名不是 username.github.io）

如果你的仓库名不是 `username.github.io`，需要配置 base 路径：

1. 在 GitHub Secrets 中添加：
   - Name: `VITE_BASE_PATH`
   - Value: `/你的仓库名/`（注意前后都有斜杠）

2. 或者直接修改 `.github/workflows/deploy.yml` 中的 `VITE_BASE_PATH` 值

## 📋 手动部署（备选方案）

如果不想使用 GitHub Actions，可以使用手动部署脚本：

```bash
./deploy-github-pages.sh https://github.com/你的用户名/你的仓库名.git https://your-api.com/api
```

然后在 GitHub Pages 设置中选择 **Deploy from a branch** → `gh-pages` 分支

## ❓ 常见问题

**Q: 部署后页面空白？**  
A: 检查 `vite.config.js` 中的 `base` 路径是否正确

**Q: API 请求失败？**  
A: 确保在 GitHub Secrets 中配置了 `VITE_API_BASE_URL`

**Q: 如何查看部署日志？**  
A: 进入仓库 → **Actions** 标签页查看部署状态

## 📚 详细文档

更多详细信息请查看：[部署到GitHub-Pages.md](./部署到GitHub-Pages.md)
