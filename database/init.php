<?php
/**
 * 数据库初始化脚本
 * 
 * 创建所有数据表并插入默认管理员账户
 * PHP 7.4+ / MySQL 5.6+
 * 
 * 使用方法：
 * php database/init.php
 */

require_once __DIR__ . '/../includes/Database.php';

// 设置 UTF-8 输出
header('Content-Type: text/html; charset=utf-8');

echo "<h1>数据库初始化</h1>\n";

try {
    $config = require __DIR__ . '/../config/config.php';
    $dbConfig = $config['database'];
    
    // 首先创建数据库（如果不存在）
    echo "<p>正在创建数据库...</p>\n";
    
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset={$dbConfig['charset']}",
        $dbConfig['user'],
        $dbConfig['password']
    );
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['name']}` 
                DEFAULT CHARACTER SET {$dbConfig['charset']} 
                COLLATE {$dbConfig['collation']}");
    
    echo "<p>✓ 数据库创建成功</p>\n";
    
    // 获取数据库实例
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 创建产品表
    echo "<p>正在创建 products 表...</p>\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT NULL,
            download_url VARCHAR(512) NOT NULL,
            backup_url VARCHAR(512) NULL,
            status TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ products 表创建成功</p>\n";
    
    // 创建前缀规则表
    echo "<p>正在创建 prefix_rules 表...</p>\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS prefix_rules (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            prefix VARCHAR(100) NOT NULL UNIQUE,
            product_id INT(11) NOT NULL,
            priority INT(11) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_prefix_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ prefix_rules 表创建成功</p>\n";
    
    // 创建管理员表
    echo "<p>正在创建 administrators 表...</p>\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS administrators (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(255) NULL,
            status TINYINT(1) DEFAULT 1,
            last_login_at DATETIME NULL,
            failed_attempts INT(11) DEFAULT 0,
            locked_until DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ administrators 表创建成功</p>\n";
    
    // 创建查询日志表
    echo "<p>正在创建 query_logs 表...</p>\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS query_logs (
            id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
            query_key VARCHAR(255) NOT NULL,
            matched_prefix VARCHAR(100) NULL,
            product_id INT(11) NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(512) NULL,
            response_time_ms INT(11) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_created_at (created_at),
            KEY idx_product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ query_logs 表创建成功</p>\n";
    
    // 检查是否已有管理员账户
    $existingAdmin = $db->fetchOne("SELECT COUNT(*) as count FROM administrators");
    
    if ($existingAdmin['count'] == 0) {
        echo "<p>正在创建默认管理员账户...</p>\n";
        
        // 默认管理员：admin / admin123
        $passwordHash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
        
        $db->execute(
            "INSERT INTO administrators (username, password_hash, email, status) 
             VALUES (:username, :password_hash, :email, :status)",
            [
                ':username' => 'admin',
                ':password_hash' => $passwordHash,
                ':email' => 'admin@example.com',
                ':status' => 1,
            ]
        );
        
        echo "<p>✓ 默认管理员账户创建成功（用户名：admin，密码：admin123）</p>\n";
    } else {
        echo "<p>✓ 管理员账户已存在，跳过创建</p>\n";
    }
    
    echo "<h2>初始化完成！</h2>\n";
    echo "<p>所有数据表已创建，系统已就绪。</p>\n";
    echo "<p><strong>默认管理员账户：</strong></p>\n";
    echo "<ul>\n";
    echo "<li>用户名：admin</li>\n";
    echo "<li>密码：admin123</li>\n";
    echo "</ul>\n";
    echo "<p><em>请尽快修改默认密码！</em></p>\n";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ 数据库错误：" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>请检查数据库配置和连接权限。</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 错误：" . htmlspecialchars($e->getMessage()) . "</p>\n";
}
