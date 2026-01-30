# 快速修复指南

## ✅ 已完成的修复

1. ✅ 修复日志路径（macOS 路径）
2. ✅ 配置文件已复制到 `/usr/local/etc/nginx/vhosts/habit-tracker.conf`
3. ✅ 移除 server_name 冲突（只保留 `habit-tracker.com`）

## 🚀 现在执行以下命令

### 1. 重启 Nginx（让配置生效）

```bash
# 方法1：使用 brew services（推荐）
brew services restart nginx

# 方法2：使用 nginx 命令
sudo nginx -s reload
```

### 2. 测试 API

```bash
# 测试健康检查接口
curl http://habit-tracker.com/api/health

# 如果返回 JSON，说明成功！
```

## 📋 如果还有问题

### 检查错误日志

```bash
tail -20 /usr/local/var/log/nginx/habit-tracker-error.log
```

### 检查 PHP-FPM 是否运行

```bash
ps aux | grep php-fpm
# 如果没有，启动它：
brew services start php
```

### 测试 PHP 是否工作

```bash
# 创建测试文件
echo "<?php phpinfo(); ?>" > /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php

# 访问测试
curl http://habit-tracker.com/test.php

# 删除测试文件
rm /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php
```

### 检查 ThinkPHP 路由

```bash
# 直接访问 index.php
curl http://habit-tracker.com/index.php

# 应该返回 ThinkPHP 的响应
```

## 🔍 配置位置

- **配置文件：** `/usr/local/etc/nginx/vhosts/habit-tracker.conf`
- **项目路径：** `/System/Volumes/Data/data/RD/habit-tracker/api/public`
- **日志路径：** `/usr/local/var/log/nginx/`

## ⚠️ 注意事项

1. 确保 `habit-tracker.com` 在 `/etc/hosts` 中指向 `127.0.0.1`
2. 确保 PHP-FPM 运行在 `127.0.0.1:9000`
3. 确保 ThinkPHP 的 `vendor` 目录存在（已安装 composer 依赖）
