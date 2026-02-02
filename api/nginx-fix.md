# Nginx 配置修复步骤

## 已完成的修复

1. ✅ 修复日志路径（从 `/var/log/nginx/` 改为 `/usr/local/var/log/nginx/`）
2. ✅ 配置文件已复制到 `/usr/local/etc/nginx/vhosts/habit-tracker.conf`

## 下一步操作

### 1. 测试配置

```bash
nginx -t
```

### 2. 重启 Nginx

```bash
# 方法1：使用 brew services
brew services restart nginx

# 方法2：使用 nginx 命令
sudo nginx -s reload
```

### 3. 测试 API

```bash
# 测试健康检查
curl http://habit-tracker.com/api/health

# 或直接访问 index.php
curl http://habit-tracker.com/index.php
```

## 如果还有问题

### 检查 Nginx 是否加载了新配置

```bash
# 查看实际加载的配置
nginx -T | grep -A 5 "habit-tracker.com"
```

### 检查错误日志

```bash
tail -f /usr/local/var/log/nginx/habit-tracker-error.log
```

### 检查 PHP-FPM 日志

```bash
tail -f /usr/local/var/log/php-fpm.log
```

## 常见问题

### 问题1: 配置文件未生效

**解决：** 确保配置文件在 `/usr/local/etc/nginx/vhosts/` 目录，并且主配置文件包含：
```nginx
include vhosts/*.conf;
```

### 问题2: 权限问题

**解决：** 确保 Nginx 可以读取文件：
```bash
ls -la /System/Volumes/Data/data/RD/habit-tracker/api/public/index.php
```

### 问题3: ThinkPHP 路由不工作

**解决：** 检查 URL 重写规则是否正确：
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```
