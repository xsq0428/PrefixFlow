<?php
/**
 * 获取当前管理员信息 API
 * 
 * GET /api/admin/me.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/AuthService.php';

$auth = new AuthService();
$auth->startSession();

$admin = $auth->getCurrentAdmin();

if ($admin === null) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => '未登录',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => $admin,
]);
