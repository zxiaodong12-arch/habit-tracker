# 变更记录

## 2026-02-10

- 新增生产环境 API 配置：`/System/Volumes/Data/data/RD/habit-tracker/vue-frontend/.env.production` 指向 `https://api.legoapi.cn/api`
- 更新前端文档的 API 配置说明：`/System/Volumes/Data/data/RD/habit-tracker/vue-frontend/README.md`
- 后端 CORS 修复说明文档更新：`/System/Volumes/Data/data/RD/habit-tracker/api/README.md`
- Nginx 侧 CORS 修复（线上配置变更，文件路径不在仓库内）：
  - 新增白名单 Origin：`https://app.legoapi.cn`
  - 预检请求 `OPTIONS` 返回 `204`
  - 在 `location ~ \.php$` 中隐藏后端 `Access-Control-*` 头，避免重复
- 新增数据库备份与恢复文档：`/System/Volumes/Data/data/RD/habit-tracker/docs/backup-restore.md`
- 根文档新增“运维与备份”章节：`/System/Volumes/Data/data/RD/habit-tracker/README.md`
- 数据库初始化修复：
  - 创建 `habit_tracker` 数据库并导入结构
  - 修正 `database.sql` 中 `emoji` 默认值以适配 MariaDB（将默认值改为 `NULL`）
