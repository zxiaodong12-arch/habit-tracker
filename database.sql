-- 习惯追踪器 MySQL 数据库表结构设计
-- 版本: 1.0
-- 创建时间: 2026-01-29

-- 创建数据库（如果不存在）
CREATE DATABASE IF NOT EXISTS `habit_tracker` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `habit_tracker`;

-- 1. 习惯表
-- 存储习惯的基本信息
CREATE TABLE IF NOT EXISTS `habits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '习惯ID（主键）',
  `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT '用户ID（预留，支持多用户）',
  `name` VARCHAR(100) NOT NULL COMMENT '习惯名称',
  `emoji` VARCHAR(10) DEFAULT '📝' COMMENT '习惯图标（Emoji）',
  `color` VARCHAR(20) DEFAULT '#10b981' COMMENT '习惯主题色（十六进制）',
  `archived` TINYINT(1) DEFAULT 0 COMMENT '是否已归档（0=否，1=是）',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_archived` (`archived`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='习惯表';

-- 2. 打卡记录表
-- 存储每天的打卡记录（将原来的 records 对象拆分为独立记录）
CREATE TABLE IF NOT EXISTS `habit_records` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID（主键）',
  `habit_id` BIGINT UNSIGNED NOT NULL COMMENT '习惯ID（外键）',
  `record_date` DATE NOT NULL COMMENT '打卡日期（YYYY-MM-DD）',
  `completed` TINYINT(1) DEFAULT 1 COMMENT '是否完成（0=未完成，1=已完成）',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_habit_date` (`habit_id`, `record_date`) COMMENT '同一习惯同一天只能有一条记录',
  KEY `idx_habit_id` (`habit_id`),
  KEY `idx_record_date` (`record_date`),
  KEY `idx_completed` (`completed`),
  CONSTRAINT `fk_habit_records_habit_id` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='打卡记录表';

-- 3. 用户表（可选，如果未来需要支持多用户）
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID（主键）',
  `username` VARCHAR(50) NOT NULL COMMENT '用户名',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `password_hash` VARCHAR(255) DEFAULT NULL COMMENT '密码哈希（预留）',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 4. 数据导出记录表（可选，用于记录导出历史）
CREATE TABLE IF NOT EXISTS `export_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '导出记录ID（主键）',
  `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT '用户ID',
  `export_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '导出时间',
  `file_name` VARCHAR(255) DEFAULT NULL COMMENT '导出文件名',
  `habits_count` INT UNSIGNED DEFAULT 0 COMMENT '导出的习惯数量',
  `records_count` INT UNSIGNED DEFAULT 0 COMMENT '导出的记录数量',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_export_date` (`export_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='导出记录表';

-- ============================================
-- 常用查询示例
-- ============================================

-- 查询用户的所有习惯（未归档）
-- SELECT * FROM habits WHERE user_id = ? AND archived = 0 ORDER BY created_at DESC;

-- 查询某个习惯的所有打卡记录
-- SELECT * FROM habit_records WHERE habit_id = ? ORDER BY record_date DESC;

-- 查询某个习惯在指定日期范围内的打卡记录
-- SELECT * FROM habit_records 
-- WHERE habit_id = ? AND record_date BETWEEN '2024-01-01' AND '2024-12-31' 
-- ORDER BY record_date DESC;

-- 查询某个习惯的连续天数（需要应用层计算）
-- SELECT record_date FROM habit_records 
-- WHERE habit_id = ? AND completed = 1 
-- ORDER BY record_date DESC;

-- 查询某个习惯的完成率统计
-- SELECT 
--   COUNT(*) as total_records,
--   SUM(completed) as completed_count,
--   ROUND(SUM(completed) * 100.0 / COUNT(*), 2) as completion_rate
-- FROM habit_records 
-- WHERE habit_id = ?;

-- 查询用户的所有习惯统计（今日完成数、总完成率等）
-- SELECT 
--   COUNT(DISTINCT h.id) as total_habits,
--   COUNT(DISTINCT CASE WHEN hr.record_date = CURDATE() AND hr.completed = 1 THEN h.id END) as today_completed,
--   COUNT(DISTINCT CASE WHEN hr.completed = 1 THEN hr.id END) as total_completed_records
-- FROM habits h
-- LEFT JOIN habit_records hr ON h.id = hr.habit_id
-- WHERE h.user_id = ? AND h.archived = 0;

-- ============================================
-- 索引优化建议
-- ============================================

-- 如果数据量很大，可以考虑以下复合索引：
-- CREATE INDEX idx_habit_date_completed ON habit_records(habit_id, record_date, completed);
-- CREATE INDEX idx_user_archived_created ON habits(user_id, archived, created_at);

-- ============================================
-- 数据迁移脚本（从 JSON 导入）
-- ============================================

-- 假设你有一个 JSON 格式的导出文件，可以通过以下方式导入：
-- 1. 解析 JSON 文件
-- 2. 插入 habits 表
-- 3. 遍历每个习惯的 records，插入 habit_records 表

-- 示例插入语句：
-- INSERT INTO habits (name, emoji, color, archived, created_at) 
-- VALUES ('喝够2L水', '💧', '#10b981', 0, '2024-01-01 00:00:00');

-- INSERT INTO habit_records (habit_id, record_date, completed) 
-- VALUES (1, '2024-01-01', 1), (1, '2024-01-02', 1), (1, '2024-01-03', 1);
