#!/bin/bash

# 使用 Personal Access Token 推送代码
# 使用方法: ./push-with-token.sh

echo "🔐 GitHub Personal Access Token 推送脚本"
echo "=========================================="
echo ""
echo "请确保你已经创建了 Personal Access Token："
echo "1. 访问: https://github.com/settings/tokens/new"
echo "2. 勾选 'repo' 权限"
echo "3. 生成并复制 token"
echo ""
read -p "按 Enter 继续..."

cd /System/Volumes/Data/data/RD/habit-tracker

echo ""
echo "正在推送代码到 GitHub..."
git push -u origin main

echo ""
echo "✅ 如果推送成功，接下来："
echo "1. 进入仓库: https://github.com/zxiaodong12-arch/habit-tracker"
echo "2. Settings → Pages → Source 选择 'GitHub Actions'"
echo "3. 保存设置，等待自动部署"
