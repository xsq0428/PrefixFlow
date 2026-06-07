<?php
/**
 * 公共查询接口
 * 
 * POST /api/query.php
 * 接收卡密，返回产品信息和下载链接
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 只接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Method not allowed. Use POST.',
        ],
    ]);
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/FileCache.php';
require_once __DIR__ . '/../includes/PrefixMatcher.php';

try {
    $startTime = microtime(true);
    
    // 获取请求数据
    $input = json_decode(file_get_contents('php://input'), true);
    $key = trim($input['key'] ?? '');
    
    // 参数验证
    if (empty($key)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => '请输入有效的卡密',
            ],
        ]);
        exit;
    }
    
    // 限制卡密长度
    if (strlen($key) > 200) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => '卡密长度不能超过 200 个字符',
            ],
        ]);
        exit;
    }
    
    // 执行前缀匹配
    $matcher = new PrefixMatcher();
    
    // 如果缓存未命中，从数据库刷新
    if ($matcher->getAllPrefixes() === null || count($matcher->getAllPrefixes()) === 0) {
        $db = Database::getInstance();
        $matcher->refresh($db);
        // 重新加载刷新后的数据
        $matcher->loadFromCache();
    }
    
    $product = $matcher->match($key);
    $matchedPrefix = null;
    
    // 找出匹配的前缀（用于日志记录）
    if ($product !== null) {
        foreach ($matcher->getAllPrefixes() as $prefix => $rule) {
            if (strpos($key, $prefix) === 0 && $rule['product_id'] === $product['id']) {
                $matchedPrefix = $prefix;
                break;
            }
        }
    }
    
    $endTime = microtime(true);
    $responseTime = (int) (($endTime - $startTime) * 1000);
    
    // 记录查询日志
    try {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO query_logs (query_key, matched_prefix, product_id, ip_address, user_agent, response_time_ms) 
             VALUES (:query_key, :matched_prefix, :product_id, :ip_address, :user_agent, :response_time_ms)",
            [
                ':query_key' => substr($key, 0, 10) . '***', // 脱敏处理
                ':matched_prefix' => $matchedPrefix,
                ':product_id' => $product['id'] ?? null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                ':response_time_ms' => $responseTime,
            ]
        );
    } catch (Exception $e) {
        // 日志记录失败不影响主流程
        error_log('Query log failed: ' . $e->getMessage());
    }
    
    // 返回结果
    if ($product !== null) {
        echo json_encode([
            'success' => true,
            'data' => [
                'matched' => true,
                'prefix' => $matchedPrefix,
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'download_url' => $product['download_url'],
                    'backup_url' => $product['backup_url'],
                ],
            ],
            'message' => '匹配成功',
            'response_time_ms' => $responseTime,
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [
                'matched' => false,
                'prefix' => null,
                'product' => null,
            ],
            'message' => '未找到匹配的产品，请检查卡密是否正确',
            'response_time_ms' => $responseTime,
        ]);
    }
    
} catch (Exception $e) {
    error_log('Query API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_ERROR',
            'message' => '系统错误，请稍后重试',
        ],
    ]);
}
