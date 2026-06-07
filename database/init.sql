-- ============================================
-- PHP 卡密识别系统 - 数据库初始化脚本
-- 适用于宝塔面板 phpMyAdmin 导入
-- 
-- 使用方法：
-- 1. 登录宝塔面板 → 数据库 → phpMyAdmin
-- 2. 选择你的数据库
-- 3. 点击"导入"标签
-- 4. 上传此文件或粘贴 SQL 内容执行
-- ============================================

-- 创建产品表
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `download_url` VARCHAR(512) NOT NULL,
  `backup_url` VARCHAR(512) NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='产品表';

-- 创建前缀规则表
CREATE TABLE IF NOT EXISTS `prefix_rules` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `prefix` VARCHAR(100) NOT NULL UNIQUE,
  `product_id` INT(11) NOT NULL,
  `priority` INT(11) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_prefix_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='前缀规则表';

-- 创建管理员表
CREATE TABLE IF NOT EXISTS `administrators` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `status` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `failed_attempts` INT(11) DEFAULT 0,
  `locked_until` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员表';

-- 创建查询日志表
CREATE TABLE IF NOT EXISTS `query_logs` (
  `id` BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
  `query_key` VARCHAR(255) NOT NULL,
  `matched_prefix` VARCHAR(100) NULL,
  `product_id` INT(11) NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(512) NULL,
  `response_time_ms` INT(11) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_created_at` (`created_at`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='查询日志表';

-- ============================================
-- 插入默认管理员账户
-- 用户名：admin
-- 密码：admin123
-- ============================================

INSERT INTO `administrators` (`username`, `password_hash`, `email`, `status`) 
VALUES ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 1)
ON DUPLICATE KEY UPDATE `username` = `username`;

-- ============================================
-- 插入示例数据（可选，不需要可删除下面内容）
-- ============================================

-- 示例产品
INSERT INTO `products` (`name`, `description`, `download_url`, `backup_url`, `status`) 
VALUES 
('专业版软件', '专业版软件下载', 'https://example.com/download/pro.zip', 'https://backup.example.com/download/pro.zip', 1),
('企业版软件', '企业版软件下载', 'https://example.com/download/enterprise.zip', NULL, 1),
('试用版软件', '试用版软件下载', 'https://example.com/download/trial.zip', NULL, 1)
ON DUPLICATE KEY UPDATE `name` = `name`;

-- 示例前缀规则
INSERT INTO `prefix_rules` (`prefix`, `product_id`, `priority`) 
VALUES 
('PRO-', 1, 10),
('PROFESSIONAL-', 1, 5),
('ENT-', 2, 10),
('ENTERPRISE-', 2, 5),
('TRIAL-', 3, 10),
('TEST-', 3, 5)
ON DUPLICATE KEY UPDATE `prefix` = `prefix`;

-- ============================================
-- 验证安装
-- 执行以下查询确认表已创建
-- ============================================

-- 查看所有表
-- SELECT * FROM information_schema.tables WHERE table_schema = DATABASE();

-- 查看管理员账户
-- SELECT id, username, email, status FROM administrators;

-- 查看产品列表
-- SELECT id, name, status FROM products;

-- 查看前缀规则
-- SELECT id, prefix, product_id, priority FROM prefix_rules;
