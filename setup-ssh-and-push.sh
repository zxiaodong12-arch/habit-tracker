#!/bin/bash

# 切换到 SSH 并推送代码
# 使用方法: ./setup-ssh-and-push.sh

echo "🔑 切换到 SSH 方式推送"
echo "======================"
echo ""

cd /System/Volumes/Data/data/RD/habit-tracker

# 切换到 SSH URL
echo "正在切换到 SSH URL..."
git remote set-url origin git@github.com:zxiaodong12-arch/habit-tracker.git

echo ""
echo "✅ 已切换到 SSH 方式"
echo ""
echo "📋 请确保你已经："
echo "1. 将 SSH 公钥添加到 GitHub: https://github.com/settings/keys"
echo "2. 你的 SSH 公钥："
echo ""
cat ~/.ssh/id_rsa.pub
echo ""
read -p "按 Enter 继续推送..."

echo ""
echo "正在推送代码..."
git push -u origin main

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ 推送成功！"
    echo ""
    echo "📋 下一步："
    echo "1. 进入仓库: https://github.com/zxiaodong12-arch/habit-tracker"
    echo "2. Settings → Pages → Source 选择 'GitHub Actions'"
    echo "3. 保存设置，等待自动部署"
else
    echo ""
    echo "❌ 推送失败，请检查："
    echo "1. SSH 密钥是否已添加到 GitHub"
    echo "2. 运行: ssh -T git@github.com 测试连接"
fi
