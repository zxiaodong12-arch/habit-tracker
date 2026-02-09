-- 习惯追踪器数据库迁移脚本 v2.0
-- 添加习惯目标设置功能
-- 执行时间: 2026-02-06

USE `habit_tracker`;

-- 为 habits 表添加目标相关字段
ALTER TABLE `habits` 
ADD COLUMN `target_type` ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'daily' COMMENT '目标类型：每天/每周/每月/每年' AFTER `archived`,
ADD COLUMN `target_count` INT UNSIGNED DEFAULT 1 COMMENT '目标完成次数' AFTER `target_type`,
ADD COLUMN `target_start_date` DATE DEFAULT NULL COMMENT '目标开始日期（用于计算周期）' AFTER `target_count`;

-- 为现有记录设置默认值
UPDATE `habits` SET 
  `target_type` = 'daily',
  `target_count` = 1,
  `target_start_date` = DATE(`created_at`)
WHERE `target_type` IS NULL;
