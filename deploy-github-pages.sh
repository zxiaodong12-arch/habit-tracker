#!/bin/bash

# GitHub Pages 手动部署脚本
# 使用方法: ./deploy-github-pages.sh [仓库URL] [API地址]

set -e

REPO_URL=${1:-""}
API_URL=${2:-""}

echo "🚀 GitHub Pages 部署脚本"
echo "========================"

# 检查是否提供了仓库URL
if [ -z "$REPO_URL" ]; then
    echo "❌ 错误: 请提供 GitHub 仓库 URL"
    echo "使用方法: ./deploy-github-pages.sh <仓库URL> [API地址]"
    echo "示例: ./deploy-github-pages.sh https://github.com/username/repo.git https://api.example.com/api"
    exit 1
fi

# 进入前端目录
cd vue-frontend

echo "📦 安装依赖..."
npm install

# 设置环境变量（如果提供了API地址）
if [ -n "$API_URL" ]; then
    echo "🔧 配置 API 地址: $API_URL"
    export VITE_API_BASE_URL="$API_URL"
fi

echo "🔨 构建项目..."
npm run build

# 进入构建输出目录
cd ../dist

echo "📝 初始化 Git（如果需要）..."
if [ ! -d ".git" ]; then
    git init
    git checkout -b gh-pages
fi

# 检查远程仓库
if ! git remote | grep -q "^origin$"; then
    echo "🔗 添加远程仓库..."
    git remote add origin "$REPO_URL"
else
    echo "🔄 更新远程仓库地址..."
    git remote set-url origin "$REPO_URL"
fi

echo "📤 提交并推送..."
git add .
git commit -m "Deploy to GitHub Pages - $(date '+%Y-%m-%d %H:%M:%S')" || echo "没有更改需要提交"
git push -u origin gh-pages --force

echo ""
echo "✅ 部署完成！"
echo ""
echo "📋 下一步："
echo "1. 进入 GitHub 仓库页面"
echo "2. 点击 Settings → Pages"
echo "3. 选择 'Deploy from a branch'"
echo "4. 选择 'gh-pages' 分支和 '/ (root)' 目录"
echo "5. 点击 Save"
echo ""
echo "🌐 你的网站将在几分钟后可用："
echo "   https://$(echo $REPO_URL | sed 's/.*github.com\///;s/\.git$//' | tr '/' '.')"
echo "   或者: https://你的用户名.github.io/仓库名"
