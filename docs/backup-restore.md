# 数据库备份与恢复

本项目在 EC2 上使用 `mysqldump` 做本地定期备份，并提供一键恢复脚本。

## 备份脚本

位置：`/usr/local/bin/backup-habit-tracker.sh`

关键点：
- 使用只读用户 `backup` 执行 `mysqldump`
- 通过 `/root/.my.cnf` 免密
- 备份文件保存至 `/var/backups/habit-tracker`
- 自动清理 7 天以前的备份
- `gzip -t` 校验压缩包完整性

## 恢复脚本

位置：`/usr/local/bin/restore-habit-tracker.sh`

关键点：
- 使用 `root` 用户执行恢复
- 通过 `/root/.my.cnf.root` 免密
- 必须使用 `--defaults-file=/root/.my.cnf.root`，避免被 `/root/.my.cnf` 覆盖

示例：
```bash
sudo /usr/local/bin/restore-habit-tracker.sh /var/backups/habit-tracker/habit_tracker-2026-02-10-100000.sql.gz
```

## 配置文件示例

`/root/.my.cnf`（备份用只读账号）：
```ini
[client]
user=backup
password=YOUR_BACKUP_PASSWORD
host=127.0.0.1
```

`/root/.my.cnf.root`（恢复用 root）：
```ini
[client]
user=root
password=YOUR_ROOT_PASSWORD
host=127.0.0.1
```

## 定时任务

每天 02:00 执行备份：
```
0 2 * * * /usr/local/bin/backup-habit-tracker.sh >> /var/log/habit-tracker-backup.log 2>&1
```

## 日志轮转

`/etc/logrotate.d/habit-tracker-backup`：
```
/var/log/habit-tracker-backup.log {
    daily
    rotate 14
    compress
    missingok
    notifempty
    copytruncate
}
```
