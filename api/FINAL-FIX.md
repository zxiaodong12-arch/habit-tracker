# 最终修复方案

## 问题诊断

1. ✅ 配置文件已存在：`/usr/local/etc/nginx/vhosts/habit-tracker.conf`
2. ✅ index.php 文件存在
3. ✅ PHP-FPM 正在运行
4. ❌ 但访问仍然返回 "File not found"

## 可能的原因

### 1. Nginx 没有重新加载配置

**解决：**
```bash
# 强制重启 Nginx
brew services stop nginx
brew services start nginx

# 或使用 sudo
sudo nginx -s stop
sudo nginx
```

### 2. 有其他 server 块优先匹配

检查是否有其他配置文件也在监听 80 端口：
```bash
grep -r "listen.*80" /usr/local/etc/nginx/vhosts/*.conf | grep -v habit-tracker
```

### 3. URL 重写规则问题

当前配置使用 `if (!-e $request_filename)`，这在某些情况下可能不工作。

## 推荐的修复方案

### 方案1：使用 try_files（推荐）

修改 `/usr/local/etc/nginx/vhosts/habit-tracker.conf`：

```nginx
location / {
    try_files $uri $uri/ /index.php?s=$uri&$args;
}
```

### 方案2：简化 URL 重写

```nginx
location / {
    if (!-f $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
    }
}
```

### 方案3：直接测试 index.php

先测试直接访问 index.php 是否工作：
```bash
curl http://habit-tracker.com/index.php
```

如果这个能工作，说明问题在 URL 重写。

## 立即执行的步骤

1. **重启 Nginx**
   ```bash
   brew services restart nginx
   ```

2. **测试直接访问**
   ```bash
   curl http://habit-tracker.com/index.php
   ```

3. **测试带参数访问**
   ```bash
   curl "http://habit-tracker.com/index.php?s=/api/health"
   ```

4. **查看错误日志**
   ```bash
   tail -f /usr/local/var/log/nginx/habit-tracker-error.log
   ```

## 如果还是不行

创建一个简单的测试文件验证 Nginx 配置：

```bash
echo "<?php echo 'PHP works!'; ?>" > /System/Volumes/Data/data/RD/habit-tracker/api/public/test.php
curl http://habit-tracker.com/test.php
```

如果这个能工作，说明问题在 ThinkPHP 路由。
