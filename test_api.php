<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/workspace/logs/web_debug.log');

require __DIR__ . '/includes/Database.php';
require __DIR__ . '/includes/FileCache.php';
require __DIR__ . '/includes/PrefixMatcher.php';

try {
    error_log('开始初始化');
    $db = Database::getInstance();
    error_log('数据库连接成功');
    
    $matcher = new PrefixMatcher();
    error_log('PrefixMatcher 创建成功');
    
    $prefixes = $matcher->getAllPrefixes();
    error_log('当前前缀数量：' .count($prefixes ?? []));
    
    if ($prefixes === null) {
        error_log('缓存为空，刷新数据库');
        $matcher->refresh($db);
        $prefixes = $matcher->getAllPrefixes();
        error_log('刷新后前缀数量：' .count($prefixes ?? []));
    }
    
    $result = $matcher->match('VIP-888');
    error_log('匹配结果：' . print_r($result, true));
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'result' => $result,
        'prefix_count' => count($prefixes ?? []),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('错误：' . $e->getMessage());
    error_log('堆栈：' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
