<?php
/**
 * 产品管理 API
 * 
 * GET/POST/PUT/DELETE /api/admin/products.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/AuthService.php';
require_once __DIR__ . '/../../includes/ProductService.php';

$auth = new AuthService();
$auth->startSession();

// 检查登录
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 获取产品列表
        $search = $_GET['search'] ?? '';
        $productService = new ProductService();
        $products = $productService->getAll($search);
        echo json_encode(['success' => true, 'data' => $products]);
        break;
        
    case 'POST':
        // 创建产品
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $downloadUrl = trim($input['download_url'] ?? '');
        $backupUrl = isset($input['backup_url']) ? trim($input['backup_url']) : null;
        $status = isset($input['status']) ? (int) $input['status'] : 1;
        
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '产品名称不能为空']);
            exit;
        }
        
        if (empty($downloadUrl)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '下载链接不能为空']);
            exit;
        }
        
        // 验证 URL 格式
        if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '下载链接格式无效']);
            exit;
        }
        
        if ($backupUrl && !filter_var($backupUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '备用链接格式无效']);
            exit;
        }
        
        try {
            $productService = new ProductService();
            $productService->create([
                'name' => $name,
                'description' => $description,
                'download_url' => $downloadUrl,
                'backup_url' => $backupUrl,
                'status' => $status,
            ]);
            
            echo json_encode(['success' => true, 'message' => '创建成功']);
        } catch (Exception $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // 更新产品
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($input)) {
            parse_str(file_get_contents('php://input'), $input);
        }
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 ID']);
            exit;
        }
        
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $downloadUrl = trim($input['download_url'] ?? '');
        $backupUrl = isset($input['backup_url']) ? trim($input['backup_url']) : null;
        $status = isset($input['status']) ? (int) $input['status'] : 1;
        
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '产品名称不能为空']);
            exit;
        }
        
        if (empty($downloadUrl)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '下载链接不能为空']);
            exit;
        }
        
        // 验证 URL 格式
        if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '下载链接格式无效']);
            exit;
        }
        
        if ($backupUrl && !filter_var($backupUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '备用链接格式无效']);
            exit;
        }
        
        try {
            $productService = new ProductService();
            $productService->update($id, [
                'name' => $name,
                'description' => $description,
                'download_url' => $downloadUrl,
                'backup_url' => $backupUrl,
                'status' => $status,
            ]);
            
            echo json_encode(['success' => true, 'message' => '更新成功']);
        } catch (Exception $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // 删除产品
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 ID']);
            exit;
        }
        
        try {
            $productService = new ProductService();
            $productService->delete($id);
            echo json_encode(['success' => true, 'message' => '删除成功']);
        } catch (Exception $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
