/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80027 (8.0.27)
 Source Host           : localhost:3306
 Source Schema         : habit_tracker

 Target Server Type    : MySQL
 Target Server Version : 80027 (8.0.27)
 File Encoding         : 65001

 Date: 10/02/2026 14:49:37
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for export_logs
-- ----------------------------
DROP TABLE IF EXISTS `export_logs`;
CREATE TABLE `export_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '导出记录ID（主键）',
  `user_id` bigint unsigned DEFAULT NULL COMMENT '用户ID',
  `export_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '导出时间',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '导出文件名',
  `habits_count` int unsigned DEFAULT '0' COMMENT '导出的习惯数量',
  `records_count` int unsigned DEFAULT '0' COMMENT '导出的记录数量',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_export_date` (`export_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='导出记录表';

-- ----------------------------
-- Records of export_logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for habit_records
-- ----------------------------
DROP TABLE IF EXISTS `habit_records`;
CREATE TABLE `habit_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID（主键）',
  `habit_id` bigint unsigned NOT NULL COMMENT '习惯ID（外键）',
  `record_date` date NOT NULL COMMENT '打卡日期（YYYY-MM-DD）',
  `completed` tinyint(1) DEFAULT '1' COMMENT '是否完成（0=未完成，1=已完成）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_habit_date` (`habit_id`,`record_date`) COMMENT '同一习惯同一天只能有一条记录',
  KEY `idx_habit_id` (`habit_id`),
  KEY `idx_record_date` (`record_date`),
  KEY `idx_completed` (`completed`),
  CONSTRAINT `fk_habit_records_habit_id` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='打卡记录表';

-- ----------------------------
-- Records of habit_records
-- ----------------------------
BEGIN;
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (1, 2, '2026-01-30', 1, '2026-01-30 15:16:19', '2026-01-30 15:46:55');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (2, 3, '2026-01-30', 1, '2026-01-30 15:47:25', '2026-01-30 15:47:25');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (3, 5, '2026-02-04', 1, '2026-02-04 16:48:10', '2026-02-04 16:48:10');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (4, 3, '2026-02-05', 1, '2026-02-05 17:20:49', '2026-02-05 17:20:49');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (6, 3, '2026-02-09', 1, '2026-02-09 11:03:48', '2026-02-09 11:03:48');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (7, 3, '2025-10-01', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (8, 3, '2025-10-03', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (9, 3, '2025-10-05', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (10, 3, '2025-10-07', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (11, 3, '2025-10-09', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (12, 3, '2025-10-11', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (13, 3, '2025-10-13', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (14, 3, '2025-10-15', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (15, 3, '2025-10-17', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (16, 3, '2025-10-19', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (17, 3, '2025-10-21', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (18, 3, '2025-10-23', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (19, 3, '2025-10-25', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (20, 3, '2025-10-27', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (21, 3, '2025-10-29', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (22, 3, '2025-11-01', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (23, 3, '2025-11-02', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (24, 3, '2025-11-03', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (25, 3, '2025-11-05', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (26, 3, '2025-11-07', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (27, 3, '2025-11-09', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (28, 3, '2025-11-10', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (29, 3, '2025-11-12', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (30, 3, '2025-11-14', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (31, 3, '2025-11-15', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (32, 3, '2025-11-17', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (33, 3, '2025-11-19', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (34, 3, '2025-11-21', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (35, 3, '2025-11-23', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (36, 3, '2025-11-24', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (37, 3, '2025-11-25', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (38, 3, '2025-11-27', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (39, 3, '2025-11-28', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (40, 3, '2025-11-29', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (41, 3, '2025-11-30', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (42, 3, '2025-11-04', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (43, 3, '2025-11-06', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (44, 3, '2025-12-01', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (45, 3, '2025-12-03', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (46, 3, '2025-12-05', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (47, 3, '2025-12-07', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (48, 3, '2025-12-09', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (49, 3, '2025-12-11', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (50, 3, '2025-12-13', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (51, 3, '2025-12-15', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (52, 3, '2025-12-17', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (53, 3, '2025-12-19', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (54, 3, '2025-12-21', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (55, 3, '2025-12-23', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (56, 3, '2025-12-25', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (57, 3, '2025-12-27', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (58, 3, '2025-12-29', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (59, 3, '2025-12-31', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (60, 3, '2025-12-02', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (61, 3, '2025-12-04', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (62, 3, '2026-01-01', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (63, 3, '2026-01-02', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (64, 3, '2026-01-03', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (65, 3, '2026-01-04', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (66, 3, '2026-01-05', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (67, 3, '2026-01-06', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (68, 3, '2026-01-07', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (69, 3, '2026-01-08', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (70, 3, '2026-01-09', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (71, 3, '2026-01-10', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (72, 3, '2026-01-11', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (73, 3, '2026-01-12', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (74, 3, '2026-01-13', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (75, 3, '2026-01-14', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (76, 3, '2026-01-15', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (77, 3, '2026-01-16', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (78, 3, '2026-01-17', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (79, 3, '2026-01-18', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (80, 3, '2026-01-19', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (81, 3, '2026-01-20', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (82, 3, '2026-01-21', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (83, 3, '2026-01-22', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (84, 3, '2026-01-23', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (85, 3, '2026-01-24', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (86, 3, '2026-01-25', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (87, 3, '2026-01-26', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (88, 3, '2026-02-01', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (89, 3, '2026-02-02', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (90, 3, '2026-02-03', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (91, 3, '2026-02-04', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (92, 3, '2026-02-06', 1, '2026-02-09 11:26:16', '2026-02-09 11:26:16');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (94, 6, '2026-02-09', 1, '2026-02-09 18:46:53', '2026-02-09 18:46:53');
INSERT INTO `habit_records` (`id`, `habit_id`, `record_date`, `completed`, `created_at`, `updated_at`) VALUES (95, 4, '2026-02-09', 1, '2026-02-09 18:47:31', '2026-02-09 18:47:31');
COMMIT;

-- ----------------------------
-- Table structure for habits
-- ----------------------------
DROP TABLE IF EXISTS `habits`;
CREATE TABLE `habits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '习惯ID（主键）',
  `user_id` bigint unsigned DEFAULT NULL COMMENT '用户ID（预留，支持多用户）',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '习惯名称',
  `emoji` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '?' COMMENT '习惯图标（Emoji）',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#10b981' COMMENT '习惯主题色（十六进制）',
  `archived` tinyint(1) DEFAULT '0' COMMENT '是否已归档（0=否，1=是）',
  `target_type` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT 'daily' COMMENT '目标类型：每天/每周/每月/每年',
  `target_count` int unsigned DEFAULT '1' COMMENT '目标完成次数',
  `target_start_date` date DEFAULT NULL COMMENT '目标开始日期（用于计算周期）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_archived` (`archived`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='习惯表';

-- ----------------------------
-- Records of habits
-- ----------------------------
BEGIN;
INSERT INTO `habits` (`id`, `user_id`, `name`, `emoji`, `color`, `archived`, `target_type`, `target_count`, `target_start_date`, `created_at`, `updated_at`) VALUES (2, 1, '喝够2L水', '💦', '#10b981', 0, 'daily', 1, NULL, '2026-01-30 14:54:36', '2026-01-30 14:55:37');
INSERT INTO `habits` (`id`, `user_id`, `name`, `emoji`, `color`, `archived`, `target_type`, `target_count`, `target_start_date`, `created_at`, `updated_at`) VALUES (3, 2, '跑步3公里', '🏃', '#1061b7', 0, 'daily', 1, '2026-02-09', '2026-01-30 15:47:24', '2026-02-09 18:44:31');
INSERT INTO `habits` (`id`, `user_id`, `name`, `emoji`, `color`, `archived`, `target_type`, `target_count`, `target_start_date`, `created_at`, `updated_at`) VALUES (4, 2, '阅读30分钟', '📖', '#10b981', 0, 'daily', 1, NULL, '2026-01-30 15:59:17', '2026-02-09 18:47:23');
INSERT INTO `habits` (`id`, `user_id`, `name`, `emoji`, `color`, `archived`, `target_type`, `target_count`, `target_start_date`, `created_at`, `updated_at`) VALUES (5, 3, '仰卧起坐', '🏋', '#b71010', 0, 'weekly', 1, '2026-02-09', '2026-02-04 16:44:17', '2026-02-09 15:34:55');
INSERT INTO `habits` (`id`, `user_id`, `name`, `emoji`, `color`, `archived`, `target_type`, `target_count`, `target_start_date`, `created_at`, `updated_at`) VALUES (6, 2, '学习一个AI知识', '📑', '#1310b7', 0, 'daily', 3, '2026-02-09', '2026-02-09 14:55:51', '2026-02-09 18:48:06');
COMMIT;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID（主键）',
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '密码哈希（预留）',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES (1, 'testuser', NULL, '$2y$10$h0v2DSRwXjXYsEn3AMMk.Ouhs4hGMlD1cwLuoKy74HvsBqLNF1xVm', '2026-01-30 15:13:26', '2026-01-30 15:13:26');
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES (2, 'legolas', NULL, '$2y$10$61w91caioXfc9StAiHU8MuVUleneCWWZrskauoCwmuY8L6S7r6PxW', '2026-01-30 15:15:24', '2026-01-30 15:15:24');
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES (3, 'lego', NULL, '$2y$10$YqQtPo7npxX4r81j.MZBveBGPK2Lol1.HwaxXL98ElqwCHjGBkJ0q', '2026-02-04 16:43:50', '2026-02-04 16:43:50');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
