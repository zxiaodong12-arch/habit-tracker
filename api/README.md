# 习惯追踪器 ThinkPHP API 文档

ThinkPHP 6.x + MySQL 后端 API

## 快速开始

### 1. 环境要求

- PHP >= 7.2.5
- MySQL >= 5.7
- Composer
- Apache/Nginx（支持 URL 重写）

### 2. 安装依赖

```bash
cd api
composer install
```

### 3. 配置环境变量

复制 `.env.example` 为 `.env` 并修改配置：

```bash
cp .env.example .env
```

编辑 `.env` 文件，设置数据库连接信息：

```ini
[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = habit_tracker
USERNAME = root
PASSWORD = your_password
HOSTPORT = 3306
CHARSET = utf8mb4
```

### 4. 创建数据库

确保已执行 `database.sql` 创建表结构：

```bash
mysql -u root -p < ../database.sql
```

### 5. 配置 Web 服务器

#### Apache

确保 `public` 目录为网站根目录，`.htaccess` 文件已包含。

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/api/public;
    index index.php;

    location / {
        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php?s=/$1 last;
        }
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. 测试 API

访问健康检查接口：
```
GET http://your-domain/api/health
```

## API 接口

### 健康检查

```
GET /api/health
```

### 习惯管理

#### 获取习惯列表
```
GET /api/habits?user_id=1&archived=false
```

#### 获取习惯详情
```
GET /api/habits/:id
```

#### 创建习惯
```
POST /api/habits
Content-Type: application/json

{
  "user_id": 1,
  "name": "喝八杯水",
  "emoji": "💧",
  "color": "#10b981",
  "archived": false
}
```

#### 更新习惯
```
PUT /api/habits/:id
Content-Type: application/json

{
  "name": "喝够2L水",
  "emoji": "💧",
  "color": "#0ea5e9"
}
```

#### 删除习惯
```
DELETE /api/habits/:id
```

#### 归档/恢复习惯
```
PATCH /api/habits/:id/archive
Content-Type: application/json

{
  "archived": true
}
```

### 打卡记录

#### 获取打卡记录
```
GET /api/records/habit/:habitId?start_date=2024-01-01&end_date=2024-12-31&completed=true
```

#### 创建/更新打卡记录
```
POST /api/records
Content-Type: application/json

{
  "habit_id": 1,
  "record_date": "2024-01-15",
  "completed": true
}
```

#### 切换打卡状态
```
POST /api/records/toggle
Content-Type: application/json

{
  "habit_id": 1,
  "record_date": "2024-01-15"
}
```

#### 删除打卡记录
```
DELETE /api/records/:id
```

### 统计信息

#### 获取习惯统计
```
GET /api/stats/habit/:habitId
```

返回：
```json
{
  "success": true,
  "data": {
    "habit_id": 1,
    "total_records": 30,
    "completed_count": 25,
    "completion_rate": 83,
    "total_days": 30,
    "first_date": "2024-01-01",
    "current_streak": 5,
    "longest_streak": 10
  }
}
```

#### 获取用户统计
```
GET /api/stats/user/:userId
```

## 数据迁移

从 JSON 导出文件导入数据到 MySQL：

```bash
php scripts/migrate.php <json文件路径> [user_id]
```

示例：
```bash
php scripts/migrate.php ../export.json 1
```

## 响应格式

### 成功响应
```json
{
  "success": true,
  "data": { ... }
}
```

### 错误响应
```json
{
  "success": false,
  "message": "错误描述",
  "error": "详细错误信息（仅开发环境）"
}
```

## 项目结构

```
api/
├── app/
│   ├── controller/          # 控制器
│   │   ├── Habits.php       # 习惯管理
│   │   ├── Records.php      # 打卡记录
│   │   └── Stats.php        # 统计信息
│   ├── model/               # 模型
│   │   ├── Habit.php
│   │   └── HabitRecord.php
│   └── BaseController.php    # 基础控制器
├── config/                  # 配置文件
│   ├── app.php
│   ├── database.php
│   └── route.php
├── public/                  # 入口目录
│   ├── index.php
│   └── .htaccess
├── scripts/                 # 脚本
│   └── migrate.php
├── composer.json
└── .env                     # 环境配置（需创建）
```

## 注意事项

1. 所有日期格式使用 `YYYY-MM-DD`
2. `archived` 字段：`0` = 未归档，`1` = 已归档
3. `completed` 字段：`0` = 未完成，`1` = 已完成
4. 删除习惯时会自动级联删除相关打卡记录（外键约束）
5. 确保 PHP 已启用必要的扩展（PDO、JSON 等）

## 开发建议

1. 使用 Postman 或类似工具测试 API
2. 生产环境建议：
   - 设置 `APP_DEBUG = false`
   - 添加身份认证（JWT）
   - 请求限流
   - 输入验证
   - 日志记录
