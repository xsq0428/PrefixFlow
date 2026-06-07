<?php
/**
 * 管理员登录 API
 * 
 * POST /api/admin/login.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/AuthService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$auth = new AuthService();
$auth->startSession();

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => '请输入用户名和密码',
    ]);
    exit;
}

$result = $auth->login($username, $password);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => '登录成功',
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $result['error'],
    ]);
}
