<?php
/**
 * 前缀规则管理 API
 * 
 * GET/POST/PUT/DELETE /api/admin/prefixes.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/AuthService.php';
require_once __DIR__ . '/../../includes/FileCache.php';

$auth = new AuthService();
$auth->startSession();

// 检查登录
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit;
}

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 获取前缀规则列表
        $rules = $db->fetchAll(
            "SELECT pr.*, p.name as product_name 
             FROM prefix_rules pr 
             LEFT JOIN products p ON pr.product_id = p.id 
             ORDER BY pr.priority DESC, pr.created_at DESC"
        );
        echo json_encode(['success' => true, 'data' => $rules]);
        break;
        
    case 'POST':
        // 创建前缀规则
        $input = json_decode(file_get_contents('php://input'), true);
        $prefix = trim($input['prefix'] ?? '');
        $productId = (int) ($input['product_id'] ?? 0);
        $priority = (int) ($input['priority'] ?? 0);
        
        if (empty($prefix)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '前缀不能为空']);
            exit;
        }
        
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '请选择产品']);
            exit;
        }
        
        // 检查前缀是否已存在
        $existing = $db->fetchOne("SELECT id FROM prefix_rules WHERE prefix = :prefix", [':prefix' => $prefix]);
        if ($existing) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => '前缀已存在']);
            exit;
        }
        
        // 检查产品是否存在
        $product = $db->fetchOne("SELECT id FROM products WHERE id = :id", [':id' => $productId]);
        if (!$product) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '产品不存在']);
            exit;
        }
        
        // 插入规则
        $db->execute(
            "INSERT INTO prefix_rules (prefix, product_id, priority) VALUES (:prefix, :product_id, :priority)",
            [':prefix' => $prefix, ':product_id' => $productId, ':priority' => $priority]
        );
        
        // 清除缓存
        (new FileCache())->delete();
        
        echo json_encode(['success' => true, 'message' => '创建成功']);
        break;
        
    case 'PUT':
        // 更新前缀规则
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($input)) {
            parse_str(file_get_contents('php://input'), $input);
        }
        $id = (int) ($_GET['id'] ?? 0);
        $prefix = trim($input['prefix'] ?? '');
        $productId = (int) ($input['product_id'] ?? 0);
        $priority = (int) ($input['priority'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 ID']);
            exit;
        }
        
        if (empty($prefix)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '前缀不能为空']);
            exit;
        }
        
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '请选择产品']);
            exit;
        }
        
        // 检查规则是否存在
        $existing = $db->fetchOne("SELECT id FROM prefix_rules WHERE id = :id", [':id' => $id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => '规则不存在']);
            exit;
        }
        
        // 检查前缀是否与其他规则冲突
        $conflict = $db->fetchOne("SELECT id FROM prefix_rules WHERE prefix = :prefix AND id != :id", [':prefix' => $prefix, ':id' => $id]);
        if ($conflict) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => '前缀已存在']);
            exit;
        }
        
        // 更新规则
        $db->execute(
            "UPDATE prefix_rules SET prefix = :prefix, product_id = :product_id, priority = :priority WHERE id = :id",
            [':prefix' => $prefix, ':product_id' => $productId, ':priority' => $priority, ':id' => $id]
        );
        
        // 清除缓存
        (new FileCache())->delete();
        
        echo json_encode(['success' => true, 'message' => '更新成功']);
        break;
        
    case 'DELETE':
        // 删除前缀规则
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 ID']);
            exit;
        }
        
        $db->execute("DELETE FROM prefix_rules WHERE id = :id", [':id' => $id]);
        
        // 清除缓存
        (new FileCache())->delete();
        
        echo json_encode(['success' => true, 'message' => '删除成功']);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
