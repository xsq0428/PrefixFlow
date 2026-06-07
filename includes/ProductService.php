<?php
/**
 * 产品管理服务类
 * 
 * 实现产品的 CRUD 操作
 * 包含唯一性校验和关联检查
 */

class ProductService
{
    private Database $db;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * 清除缓存
     */
    private function clearCache(): void
    {
        require_once __DIR__ . '/FileCache.php';
        (new FileCache())->delete();
    }
    
    /**
     * 获取所有产品
     * 
     * @param string $search 搜索关键词（可选）
     * @return array 产品列表
     */
    public function getAll(string $search = ''): array
    {
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM prefix_rules WHERE product_id = p.id) AS rule_count
                FROM products p";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE p.name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * 根据 ID 获取产品
     * 
     * @param int $id 产品 ID
     * @return array|null 产品信息或 null
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM products WHERE id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    /**
     * 创建产品
     * 
     * @param array $data 产品数据
     * @return int 产品 ID
     * @throws Exception 如果产品名称已存在
     */
    public function create(array $data): int
    {
        // 检查名称唯一性
        if ($this->nameExists($data['name'])) {
            throw new Exception('Product name already exists');
        }
        
        $sql = "INSERT INTO products (name, description, download_url, backup_url, status) 
                VALUES (:name, :description, :download_url, :backup_url, :status)";
        
        $params = [
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':download_url' => $data['download_url'],
            ':backup_url' => $data['backup_url'] ?? null,
            ':status' => $data['status'] ?? 1,
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新产品
     * 
     * @param int $id 产品 ID
     * @param array $data 产品数据
     * @return bool 是否成功
     * @throws Exception 如果产品不存在或名称冲突
     */
    public function update(int $id, array $data): bool
    {
        // 检查产品是否存在
        $existing = $this->getById($id);
        if ($existing === null) {
            throw new Exception('Product not found');
        }
        
        // 检查名称唯一性（排除自身）
        if ($data['name'] !== $existing['name'] && $this->nameExists($data['name'])) {
            throw new Exception('Product name already exists');
        }
        
        $sql = "UPDATE products 
                SET name = :name, 
                    description = :description, 
                    download_url = :download_url, 
                    backup_url = :backup_url, 
                    status = :status 
                WHERE id = :id";
        
        $params = [
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':download_url' => $data['download_url'],
            ':backup_url' => $data['backup_url'] ?? null,
            ':status' => $data['status'] ?? 1,
        ];
        
        $this->db->execute($sql, $params);
        $this->clearCache();
        return true;
    }
    
    /**
     * 删除产品
     * 
     * @param int $id 产品 ID
     * @return bool 是否成功
     * @throws Exception 如果产品存在关联的前缀规则
     */
    public function delete(int $id): bool
    {
        // 检查是否存在关联的前缀规则
        $count = $this->db->fetchValue(
            "SELECT COUNT(*) FROM prefix_rules WHERE product_id = :id",
            [':id' => $id]
        );
        
        if ($count > 0) {
            throw new Exception('Cannot delete product with associated prefix rules');
        }
        
        $sql = "DELETE FROM products WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        $this->clearCache();
        return true;
    }
    
    /**
     * 检查产品名称是否存在
     * 
     * @param string $name 产品名称
     * @param int|null $excludeId 排除的产品 ID（更新时使用）
     * @return bool
     */
    private function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM products WHERE name = :name";
        $params = [':name' => $name];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $count = (int) $this->db->fetchValue($sql, $params);
        return $count > 0;
    }
}
