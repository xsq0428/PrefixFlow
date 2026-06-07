<?php
/**
 * 数据库连接类（PDO 封装）
 * 
 * 单例模式，支持连接池和错误处理
 * 兼容 PHP 7.4+ / MySQL 5.6+
 */

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;
    
    /**
     * 构造函数（私有，防止外部实例化）
     */
    private function __construct()
    {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->connect();
    }
    
    /**
     * 禁止克隆
     */
    private function __clone() {}
    
    /**
     * 禁止反序列化
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
    
    /**
     * 获取单例实例
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 建立数据库连接
     */
    private function connect(): void
    {
        $db = $this->config['database'];
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['name'],
            $db['charset']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true, // 持久连接
        ];
        
        try {
            $this->connection = new PDO($dsn, $db['user'], $db['password'], $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed. Please check configuration.');
        }
    }
    
    /**
     * 获取 PDO 连接对象
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * 执行查询并返回所有结果
     * 
     * @param string $sql SQL 语句
     * @param array $params 参数数组
     * @return array 结果数组
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * 执行查询并返回单行结果
     * 
     * @param string $sql SQL 语句
     * @param array $params 参数数组
     * @return array|null 结果数组或 null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * 执行查询并返回单个值
     * 
     * @param string $sql SQL 语句
     * @param array $params 参数数组
     * @return mixed 单个值
     */
    public function fetchValue(string $sql, array $params = [])
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * 执行 SQL 语句
     * 
     * @param string $sql SQL 语句
     * @param array $params 参数数组
     * @return PDOStatement 预处理语句对象
     */
    public function execute(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database query failed: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            error_log('Params: ' . json_encode($params));
            throw new Exception('Database query failed.');
        }
    }
    
    /**
     * 获取最后插入的 ID
     */
    public function lastInsertId(): int
    {
        return (int) $this->connection->lastInsertId();
    }
    
    /**
     * 开启事务
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * 提交事务
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }
    
    /**
     * 回滚事务
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
    
    /**
     * 在事务闭包中执行
     * 
     * @param Closure $callback 闭包函数
     * @return mixed 闭包返回值
     */
    public function transaction(Closure $callback)
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
