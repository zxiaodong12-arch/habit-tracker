#!/bin/bash

# habit-tracker 快速部署脚本
# 
# 使用方法:
#   开发环境: ./deploy.sh dev
#   生产环境: ./deploy.sh prod <API地址>
#
# API 地址示例:
#   - 使用域名: https://api.yourdomain.com/api
#   - 使用 IP: http://123.456.789.0:8080/api
#   - 本地测试: http://localhost:8080/api
#
# 注意: API 地址是后端 ThinkPHP API 的访问地址，需要根据你的实际部署情况填写

ENV=${1:-prod}
API_URL=${2:-""}

echo "🚀 开始部署 habit-tracker 前端..."
echo "环境: $ENV"

# 检查 Node.js
if ! command -v node &> /dev/null; then
    echo "❌ 错误: 未找到 Node.js，请先安装 Node.js 16+"
    exit 1
fi

# 进入前端目录
cd vue-frontend || exit 1

# 安装依赖
echo "📦 安装依赖..."
npm install

# 创建环境变量文件
if [ "$ENV" = "prod" ]; then
    if [ -z "$API_URL" ]; then
        echo ""
        echo "⚠️  警告: 未指定 API 地址，将使用默认值"
        echo ""
        echo "📝 API 地址说明:"
        echo "   API 地址是你的后端 ThinkPHP API 的访问地址"
        echo "   根据你的部署情况，可能是以下之一:"
        echo "   - 使用域名: https://api.yourdomain.com/api"
        echo "   - 使用 IP:  http://你的服务器IP:8080/api"
        echo "   - 本地测试: http://localhost:8080/api"
        echo ""
        echo "   使用方法: ./deploy.sh prod <你的API地址>"
        echo "   示例: ./deploy.sh prod http://123.456.789.0:8080/api"
        echo ""
        read -p "是否继续使用默认值? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo "❌ 已取消，请重新运行并指定 API 地址"
            exit 1
        fi
    else
        echo "📝 创建生产环境配置文件..."
        cat > .env.production << EOF
VITE_API_BASE_URL=$API_URL
EOF
        echo "✅ API 地址已设置为: $API_URL"
        echo ""
        echo "💡 提示: 确保这个地址可以从用户浏览器访问到你的后端 API"
    fi
fi

# 构建
echo "🔨 构建生产版本..."
npm run build

if [ $? -eq 0 ]; then
    echo "✅ 构建成功！"
    echo ""
    echo "📁 构建文件位置: ../dist/"
    echo ""
    echo "下一步："
    echo "1. 如果使用 COS: 上传 dist/ 目录内容到 COS 存储桶"
    echo "2. 如果使用 CVM: 使用 scp 上传 dist/ 目录内容到服务器"
    echo ""
    echo "示例命令（CVM）:"
    echo "  scp -r dist/* root@你的服务器IP:/var/www/habit-tracker/frontend/"
else
    echo "❌ 构建失败，请检查错误信息"
    exit 1
fi
