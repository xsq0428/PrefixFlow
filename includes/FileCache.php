<?php
/**
 * 文件缓存服务类
 * 
 * 使用文件缓存前缀规则和产品数据
 * 兼容 PHP 7.4+ / MySQL 5.6+，不依赖 Redis
 */

class FileCache
{
    private string $cacheDir;
    private int $ttl;
    private string $cacheFile;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->cacheDir = $config['cache']['dir'];
        $this->ttl = $config['cache']['ttl'];
        $this->cacheFile = $this->cacheDir . '/prefix_rules.cache.php';
        
        // 确保缓存目录存在
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * 从缓存获取数据
     * 
     * @return array|null 缓存数据或 null（缓存失效时）
     */
    public function get(): ?array
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }
        
        $fileMtime = filemtime($this->cacheFile);
        if (time() - $fileMtime > $this->ttl) {
            return null;
        }
        
        try {
            $data = require $this->cacheFile;
            if (!is_array($data) || !isset($data['version'])) {
                return null;
            }
            return $data;
        } catch (Throwable $e) {
            error_log('Cache load failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 写入缓存
     * 
     * @param array $prefixes 前缀规则数组
     * @param array $products 产品数组
     * @return bool 是否成功
     */
    public function set(array $prefixes, array $products): bool
    {
        $data = [
            'version' => time(),
            'prefixes' => $prefixes,
            'products' => $products,
        ];
        
        // 生成 PHP 数组格式的缓存文件
        $content = "<?php\nreturn " . var_export($data, true) . ";\n";
        
        // 使用文件锁保证写入原子性
        $tempFile = $this->cacheDir . '/.cache.tmp.' . uniqid();
        
        try {
            // 写入临时文件
            $bytes = file_put_contents($tempFile, $content, LOCK_EX);
            if ($bytes === false) {
                throw new Exception('Failed to write cache file');
            }
            
            // 原子性替换
            if (!rename($tempFile, $this->cacheFile)) {
                throw new Exception('Failed to move cache file');
            }
            
            // 设置文件权限
            chmod($this->cacheFile, 0644);
            
            return true;
        } catch (Throwable $e) {
            error_log('Cache write failed: ' . $e->getMessage());
            // 清理临时文件
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            return false;
        }
    }
    
    /**
     * 清除缓存
     * 
     * @return bool 是否成功
     */
    public function delete(): bool
    {
        if (file_exists($this->cacheFile)) {
            return unlink($this->cacheFile);
        }
        return true;
    }
    
    /**
     * 检查缓存是否存在
     * 
     * @return bool
     */
    public function has(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $fileMtime = filemtime($this->cacheFile);
        return (time() - $fileMtime) <= $this->ttl;
    }
}
