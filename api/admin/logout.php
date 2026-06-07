<?php
/**
 * 管理员登出 API
 * 
 * POST /api/admin/logout.php
 */

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/AuthService.php';

$auth = new AuthService();
$auth->startSession();
$auth->logout();

echo json_encode([
    'success' => true,
    'message' => '登出成功',
]);
