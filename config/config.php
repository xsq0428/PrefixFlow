<?php
/**
 * 系统配置文件
 * 
 * PHP 7.4+ / MySQL 5.6+
 */

// 加载 .env 文件
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    foreach ($envVars as $key => $value) {
        putenv("{$key}={$value}");
    }
}

return [
    // 数据库配置
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'license_system',
        'user' => getenv('DB_USER') ?: 'license',
        'password' => getenv('DB_PASSWORD') ?: 'license123',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
    ],
    
    // 应用配置
    'app' => [
        'name' => '卡密识别系统',
        'version' => '1.0.0',
        'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false,
        'timezone' => 'Asia/Shanghai',
    ],
    
    // 会话配置
    'session' => [
        'name' => 'LICENSE_ADMIN_SID',
        'lifetime' => 1800, // 30 分钟
        'path' => '/',
        'domain' => '',
        'secure' => false, // 生产环境应设置为 true
        'httponly' => true,
    ],
    
    // 缓存配置
    'cache' => [
        'dir' => __DIR__ . '/../cache',
        'ttl' => 300, // 缓存有效期 5 分钟
    ],
    
    // 日志配置
    'log' => [
        'dir' => __DIR__ . '/../logs',
        'level' => getenv('LOG_LEVEL') ?: 'INFO',
        'max_files' => 30, // 保留最近 30 天的日志
    ],
    
    // 安全配置
    'security' => [
        'password_cost' => 12, // bcrypt cost
        'login_max_attempts' => 5, // 最大登录失败次数
        'login_lockout_time' => 900, // 锁定时间 15 分钟（秒）
        'csrf_token_name' => 'csrf_token',
    ],
];
