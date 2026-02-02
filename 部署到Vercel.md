# 部署到 Vercel - 最简单的方法

## 为什么选择 Vercel？

- ✅ **完全免费**
- ✅ **自动 HTTPS**
- ✅ **自动配置 Vue Router**（不需要 Hash 模式）
- ✅ **全球 CDN 加速**
- ✅ **部署超简单**（3 步完成）
- ✅ **不会下载文件**（完美解决 CloudBase 的问题）

## 部署步骤（5 分钟）

### 方法一：通过网页部署（最简单）

1. **访问 Vercel**
   - 打开：https://vercel.com
   - 使用 GitHub/GitLab/Bitbucket 账号登录（或注册）

2. **导入项目**
   - 点击 "Add New" → "Project"
   - 如果代码在 GitHub，选择仓库
   - 如果代码在本地，先推送到 GitHub

3. **配置项目**
   - **Framework Preset**: 选择 "Vite"
   - **Root Directory**: `vue-frontend`（如果项目在子目录）
   - **Build Command**: `npm run build`（自动检测）
   - **Output Directory**: `dist`（自动检测）
   - **Install Command**: `npm install`（自动检测）

4. **环境变量（可选）**
   - 如果需要，添加 `VITE_API_BASE_URL`
   - 例如：`http://1.15.12.78/api`

5. **点击 Deploy**
   - 等待 1-2 分钟
   - 部署完成后会得到一个 URL，例如：`https://your-project.vercel.app`

6. **完成！**
   - 访问 URL，应该可以正常显示
   - URL 格式：`https://your-project.vercel.app/login`（无 `#` 符号）

### 方法二：通过命令行部署

```bash
# 1. 安装 Vercel CLI
npm install -g vercel

# 2. 登录
vercel login

# 3. 进入前端目录
cd vue-frontend

# 4. 部署
vercel

# 按提示操作：
# - Set up and deploy? Y
# - Which scope? 选择你的账号
# - Link to existing project? N
# - Project name? 输入项目名（或直接回车）
# - Directory? ./dist（或直接回车）
# - Override settings? N

# 5. 部署完成，会得到一个 URL
```

### 方法三：通过 GitHub 自动部署

1. **推送代码到 GitHub**
   ```bash
   git add .
   git commit -m "准备部署到 Vercel"
   git push
   ```

2. **在 Vercel 导入项目**
   - 选择 GitHub 仓库
   - Vercel 会自动检测 Vue 项目配置
   - 点击 Deploy

3. **自动部署**
   - 以后每次 push 代码，Vercel 会自动重新部署

## 配置说明

### vercel.json（已创建）

这个文件已经创建好了，包含：
- 路由重写规则（支持 Vue Router History 模式）
- 正确的 Content-Type 头
- 静态资源缓存配置

### 路由模式

代码已修改为：
- **生产环境**：使用 History 模式（URL 无 `#`）
- **开发环境**：使用 Hash 模式

## 访问地址

部署完成后，你会得到：
- **生产地址**：`https://your-project.vercel.app`
- **预览地址**：每次部署都有独立的预览 URL

## 自定义域名（可选）

1. 在 Vercel 项目设置中
2. 添加自定义域名
3. 配置 DNS 记录
4. Vercel 自动配置 HTTPS

## 环境变量配置

如果需要配置 API 地址：

1. Vercel 项目 → Settings → Environment Variables
2. 添加：
   - **Name**: `VITE_API_BASE_URL`
   - **Value**: `http://1.15.12.78/api`（或你的 API 地址）
3. 重新部署

## 优势对比

| 特性 | CloudBase | Vercel |
|------|-----------|--------|
| 部署难度 | 中等 | ⭐ 非常简单 |
| 路由支持 | 需要配置 | ⭐ 自动支持 |
| HTML 下载问题 | ❌ 有 | ✅ 无 |
| 免费额度 | 有限 | ⭐ 更宽松 |
| 国内访问 | 快 | 中等 |
| 自动 HTTPS | ✅ | ✅ |
| 自动部署 | 需要配置 | ⭐ 自动 |

## 常见问题

### Q: Vercel 在国内访问慢吗？

**A:** 通常还可以，如果慢可以配置 CDN 或使用自定义域名。

### Q: 需要修改代码吗？

**A:** 不需要！代码已经配置好了，直接部署即可。

### Q: 可以继续使用 Hash 模式吗？

**A:** 可以，但 Vercel 支持 History 模式，建议使用 History 模式（URL 更美观）。

### Q: 如何更新部署？

**A:** 
- 如果连接了 GitHub：push 代码自动部署
- 如果使用 CLI：运行 `vercel --prod`

## 总结

**推荐使用 Vercel**，因为：
1. 部署最简单
2. 自动处理所有路由问题
3. 不会下载 HTML 文件
4. 完全免费
5. 对 Vue 项目支持完美

**下一步**：
1. 访问 https://vercel.com 注册/登录
2. 导入项目或使用 CLI 部署
3. 等待 1-2 分钟
4. 完成！
