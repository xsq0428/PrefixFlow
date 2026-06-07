<?php
/**
 * 前缀匹配服务类
 * 
 * 实现最长前缀匹配算法
 * 从缓存加载前缀规则
 */

class PrefixMatcher
{
    private ?FileCache $cache = null;
    private ?array $prefixes = null;
    private ?array $products = null;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->cache = new FileCache();
        $this->loadFromCache();
        
        if ($this->prefixes === null || $this->products === null) {
            $this->initFromDatabase();
        }
    }
    
    /**
     * 从数据库初始化数据（当缓存不可用时）
     */
    private function initFromDatabase(): void
    {
        try {
            require_once __DIR__ . '/Database.php';
            $db = Database::getInstance();
            $this->refresh($db);
        } catch (Exception $e) {
            error_log('PrefixMatcher init from database failed: ' . $e->getMessage());
        }
    }
    
    /**
     * 从缓存加载数据
     */
    private function loadFromCache(): void
    {
        $data = $this->cache->get();
        
        if ($data !== null) {
            $this->prefixes = $data['prefixes'] ?? [];
            $this->products = $data['products'] ?? [];
        }
    }
    
    /**
     * 刷新缓存数据
     * 
     * @param Database $db 数据库连接
     */
    public function refresh(Database $db): void
    {
        // 从数据库加载所有产品
        $products = $db->fetchAll(
            "SELECT id, name, download_url, backup_url, status 
             FROM products 
             WHERE status = 1"
        );
        
        $this->products = [];
        foreach ($products as $product) {
            $this->products[$product['id']] = $product;
        }
        
        // 从数据库加载所有前缀规则
        $rules = $db->fetchAll(
            "SELECT prefix, product_id, priority 
             FROM prefix_rules 
             ORDER BY priority DESC"
        );
        
        $this->prefixes = [];
        foreach ($rules as $rule) {
            $this->prefixes[$rule['prefix']] = [
                'product_id' => (int) $rule['product_id'],
                'priority' => (int) $rule['priority'],
            ];
        }
        
        // 按前缀长度降序排序（优先匹配长前缀）
        uksort($this->prefixes, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        // 保存到缓存
        $this->cache->set($this->prefixes, $this->products);
    }
    
    /**
     * 匹配卡密前缀
     * 
     * 采用最长前缀匹配原则
     * 
     * @param string $key 卡密
     * @return array|null 匹配的产品信息或 null
     */
    public function match(string $key): ?array
    {
        if ($this->prefixes === null || $this->products === null) {
            $this->initFromDatabase();
        }
        
        if ($this->prefixes === null || $this->products === null) {
            return null;
        }
        
        $key = trim($key);
        if (empty($key)) {
            return null;
        }
        
        // 限制最大匹配长度
        $maxLength = min(strlen($key), 100);
        
        // 从最长前缀开始尝试匹配
        for ($length = $maxLength; $length >= 1; $length--) {
            $prefix = substr($key, 0, $length);
            
            if (isset($this->prefixes[$prefix])) {
                $productId = $this->prefixes[$prefix]['product_id'];
                
                if (isset($this->products[$productId])) {
                    return $this->products[$productId];
                }
            }
        }
        
        return null;
    }
    
    /**
     * 获取所有前缀规则（用于调试）
     * 
     * @return array
     */
    public function getAllPrefixes(): array
    {
        return $this->prefixes ?? [];
    }
    
    /**
     * 获取所有产品（用于调试）
     * 
     * @return array
     */
    public function getAllProducts(): array
    {
        return $this->products ?? [];
    }
}
