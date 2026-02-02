#!/bin/bash

# 测试 SSH 连接并推送代码
echo "🔍 测试 SSH 连接..."
echo "===================="
echo ""

# 测试 SSH 连接
ssh_output=$(ssh -T git@github.com 2>&1)
ssh_status=$?

if [ $ssh_status -eq 0 ] || echo "$ssh_output" | grep -q "successfully authenticated"; then
    echo "✅ SSH 连接成功！"
    echo ""
    echo "正在切换到 SSH URL..."
    cd /System/Volumes/Data/data/RD/habit-tracker
    git remote set-url origin git@github.com:zxiaodong12-arch/habit-tracker.git
    
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
    fi
else
    echo "❌ SSH 连接失败"
    echo ""
    echo "请确保："
    echo "1. 已将 SSH 公钥添加到 GitHub: https://github.com/settings/keys"
    echo "2. 你的 SSH 公钥："
    echo ""
    cat ~/.ssh/id_rsa.pub
    echo ""
    echo "或者使用 Personal Access Token 方式（更快）："
    echo "1. 访问: https://github.com/settings/tokens/new"
    echo "2. 勾选 'repo' 权限，生成 token"
    echo "3. 运行: git push -u origin main"
    echo "   用户名: zxiaodong12@gmail.com"
    echo "   密码: 粘贴你的 token"
fi
