#!/usr/bin/env php
<?php
/**
 * 环境检查脚本
 * 
 * 使用方法：
 * php test/environment.php
 */

echo "=====================================\n";
echo "  PHP 卡密识别系统 - 环境检查工具\n";
echo "=====================================\n\n";

$errors = [];
$warnings = [];

// 检查 PHP 版本
echo "1. 检查 PHP 版本...\n";
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "   ✓ PHP 版本：{$phpVersion}\n";
} else {
    echo "   ✗ PHP 版本过低：{$phpVersion} (需要 >= 7.4.0)\n";
    $errors[] = 'PHP 版本过低';
}
echo "\n";

// 检查必需的 PHP 扩展
echo "2. 检查 PHP 扩展...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'openssl', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ {$ext}\n";
    } else {
        echo "   ✗ 缺少扩展：{$ext}\n";
        $errors[] = "缺少 PHP 扩展：{$ext}";
    }
}
echo "\n";

// 检查目录权限
echo "3. 检查目录权限...\n";
$directories = [
    __DIR__ . '/cache' => '缓存目录',
    __DIR__ . '/logs' => '日志目录',
    __DIR__ . '/config' => '配置目录',
];

foreach ($directories as $dir => $name) {
    if (!is_dir($dir)) {
        echo "   ✗ {$name}不存在：{$dir}\n";
        $errors[] = "{$name}不存在";
    } elseif (!is_writable($dir)) {
        echo "   ⚠ {$name}不可写：{$dir}\n";
        $warnings[] = "{$name}不可写";
    } else {
        echo "   ✓ {$name}正常\n";
    }
}
echo "\n";

// 检查配置文件
echo "4. 检查配置文件...\n";
$configFile = __DIR__ . '/config/config.php';
if (file_exists($configFile)) {
    echo "   ✓ 配置文件存在\n";
    
    // 检查 .env 文件
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        echo "   ✓ .env 文件存在\n";
        
        // 读取 .env 配置
        $envContent = file_get_contents($envFile);
        if (strpos($envContent, 'DB_PASSWORD=your_password_here') !== false) {
            echo "   ⚠ 警告：请修改默认数据库密码\n";
            $warnings[] = '使用默认数据库密码';
        }
    } else {
        echo "   ⚠ .env 文件不存在（请复制 .env.example）\n";
        $warnings[] = '缺少 .env 文件';
    }
} else {
    echo "   ✗ 配置文件不存在\n";
    $errors[] = '配置文件不存在';
}
echo "\n";

// 检查数据库连接
echo "5. 检查数据库连接...\n";
try {
    if (file_exists(__DIR__ . '/../.env')) {
        $env = parse_ini_string(file_get_contents(__DIR__ . '/../.env'));
        
        $host = $env['DB_HOST'] ?? 'localhost';
        $port = $env['DB_PORT'] ?? '3306';
        $name = $env['DB_NAME'] ?? 'license_system';
        $user = $env['DB_USER'] ?? 'root';
        $password = $env['DB_PASSWORD'] ?? '';
        
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "   ✓ 数据库连接成功\n";
        
        // 检查表是否存在
        $requiredTables = ['administrators', 'prefix_rules', 'products', 'query_logs'];
        $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($requiredTables as $table) {
            if (in_array($table, $existingTables)) {
                echo "   ✓ 表 {$table} 存在\n";
            } else {
                echo "   ✗ 表 {$table} 不存在（请运行 database/init.php）\n";
                $errors[] = "表 {$table} 不存在";
            }
        }
        
        // 检查管理员账户
        $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM administrators")->fetchColumn();
        if ($adminCount > 0) {
            echo "   ✓ 管理员账户已创建\n";
        } else {
            echo "   ⚠ 尚无管理员账户（请运行 database/init.php）\n";
            $warnings[] = '缺少管理员账户';
        }
        
    } else {
        echo "   ⚠ .env 文件不存在，跳过数据库检查\n";
    }
} catch (PDOException $e) {
    echo "   ✗ 数据库连接失败：" . $e->getMessage() . "\n";
    $errors[] = '数据库连接失败';
}
echo "\n";

// 检查关键文件
echo "6. 检查关键文件...\n";
$files = [
    __DIR__ . '/../public/index.php' => '查询首页',
    __DIR__ . '/../admin/login.php' => '后台登录页',
    __DIR__ . '/../includes/Database.php' => '数据库类',
    __DIR__ . '/../includes/FileCache.php' => '缓存类',
    __DIR__ . '/../includes/PrefixMatcher.php' => '前缀匹配类',
];

foreach ($files as $file => $name) {
    if (file_exists($file)) {
        echo "   ✓ {$name}\n";
    } else {
        echo "   ✗ {$name}缺失\n";
        $errors[] = "文件缺失：{$name}";
    }
}
echo "\n";

// 总结
echo "=====================================\n";
echo "  检查结果\n";
echo "=====================================\n\n";

if (empty($errors) && empty($warnings)) {
    echo "🎉 所有检查通过！系统已就绪。\n\n";
    echo "下一步：\n";
    echo "1. 如果尚未初始化数据库，请访问 /database/init.php\n";
    echo "2. 访问首页：http://your-domain/\n";
    echo "3. 登录后台：http://your-domain/admin/login.php\n";
    echo "   默认账户：admin / admin123\n";
    exit(0);
}

if (!empty($errors)) {
    echo "❌ 发现 " . count($errors) . " 个错误：\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    echo "\n请先修复这些错误！\n\n";
}

if (!empty($warnings)) {
    echo "⚠️  发现 " . count($warnings) . " 个警告：\n";
    foreach ($warnings as $warning) {
        echo "   - {$warning}\n";
    }
    echo "\n建议处理这些警告以确保系统正常运行。\n\n";
}

if (!empty($errors)) {
    exit(1);
}

exit(0);
