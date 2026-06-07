<?php
/**
 * 一键安装接口
 * 
 * POST /api/install.php
 * 接收数据库配置，创建数据库、表结构并初始化数据
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$dbName    = trim($input['db_name'] ?? '');
$dbHost    = trim($input['db_host'] ?? 'localhost');
$dbPort    = (int) ($input['db_port'] ?? 3306);
$dbUser    = trim($input['db_user'] ?? '');
$dbPass    = $input['db_password'] ?? '';
$adminUser = trim($input['admin_username'] ?? 'admin');
$adminPass = $input['admin_password'] ?? 'admin123';
$installSample = (bool) ($input['install_sample'] ?? true);

// 验证必填项
$errors = [];
if (empty($dbName)) $errors[] = '数据库名称不能为空';
if (empty($dbUser)) $errors[] = '数据库用户名不能为空';
if (empty($dbPass)) $errors[] = '数据库密码不能为空';
if (empty($adminUser)) $errors[] = '管理员用户名不能为空';
if (empty($adminPass)) $errors[] = '管理员密码不能为空';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('；', $errors)]);
    exit;
}

try {
    // 第一步：测试数据库连接（不指定库名）
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $dbHost, $dbPort);
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // 第二步：创建数据库
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . addslashes($dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `" . addslashes($dbName) . "`");
    
    // 第三步：创建表结构
    $sqlFile = __DIR__ . '/../database/init.sql';
    
    // 读取 SQL 文件，移除示例数据部分（根据选项）
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception('读取数据库初始化文件失败');
    }
    
    // 解析并执行 SQL 语句
    // 替换示例数据 ON DUPLICATE KEY 逻辑
    if (!$installSample) {
        // 移除示例数据的 ON DUPLICATE KEY UPDATE 部分
        $sql = preg_replace('/ON DUPLICATE KEY UPDATE[^;]+;/', ';', $sql);
        // 移除注释中的示例说明（可选）
    }
    
    // 按分号分割 SQL 语句
    $statements = array_filter(array_map('trim', preg_split("/;[\r\n]+/", $sql)));
    
    foreach ($statements as $stmt) {
        if (empty($stmt) || strpos($stmt, '--') === 0) continue;
        // 去除行内注释
        $cleanStmt = preg_replace('/--.*$/', '', $stmt);
        if (trim($cleanStmt) === '') continue;
        $pdo->exec($cleanStmt);
    }
    
    // 第四步：替换默认管理员密码
    $newHash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->exec(
        "UPDATE `administrators` SET `password_hash` = '" . addslashes($newHash) . "', `username` = '" . addslashes($adminUser) . "'"
    );
    
    // 第五步：生成 .env 文件
    $envContent = sprintf(
        "# 环境变量配置\n\n# 数据库配置\nDB_HOST=%s\nDB_PORT=%d\nDB_NAME=%s\nDB_USER=%s\nDB_PASSWORD=%s\n\n# 应用配置\nAPP_DEBUG=false\n\n# 日志级别\nLOG_LEVEL=INFO\n",
        $dbHost, $dbPort, $dbName, $dbUser, $dbPass
    );
    
    $envFile = __DIR__ . '/../.env';
    file_put_contents($envFile, $envContent);
    
    // 第六步：确保缓存和日志目录权限
    @mkdir(__DIR__ . '/../cache', 0755, true);
    @mkdir(__DIR__ . '/../logs', 0755, true);
    @chmod(__DIR__ . '/../cache', 0755);
    @chmod(__DIR__ . '/../logs', 0755);
    
    echo json_encode([
        'success' => true,
        'message' => '安装成功！数据库已创建，表结构已初始化。',
        'next'    => '/index.php',
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '数据库错误：' . $e->getMessage(),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '安装失败：' . $e->getMessage(),
    ]);
}
