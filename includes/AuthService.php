<?php
/**
 * 管理员认证服务类
 * 
 * 处理登录、登出、会话管理
 * 包含登录失败锁定机制
 */

class AuthService
{
    private Database $db;
    private array $config;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = require __DIR__ . '/../config/config.php';
    }
    
    /**
     * 初始化管理会话
     */
    public function startSession(): void
    {
        $sessionConfig = $this->config['session'];
        
        session_name($sessionConfig['name']);
        ini_set('session.cookie_lifetime', $sessionConfig['lifetime']);
        ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
        ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * 管理员登录
     * 
     * @param string $username 用户名
     * @param string $password 密码
     * @return array 登录结果
     */
    public function login(string $username, string $password): array
    {
        try {
            // 获取管理员信息
            $admin = $this->db->fetchOne(
                "SELECT * FROM administrators WHERE username = :username",
                [':username' => $username]
            );
            
            if ($admin === null) {
                return [
                    'success' => false,
                    'error' => '用户名或密码错误',
                ];
            }
            
            // 检查账户是否被禁用
            if ($admin['status'] === 0) {
                return [
                    'success' => false,
                    'error' => '账户已被禁用，请联系管理员',
                ];
            }
            
            // 检查账户是否被锁定
            if ($admin['locked_until'] !== null) {
                $lockedUntil = new DateTime($admin['locked_until']);
                if ($lockedUntil > new DateTime()) {
                    return [
                        'success' => false,
                        'error' => '账户已被锁定，请在 ' . $lockedUntil->format('Y-m-d H:i:s') . ' 后重试',
                    ];
                } else {
                    // 锁定时间已过，重置失败次数
                    $this->db->execute(
                        "UPDATE administrators SET failed_attempts = 0, locked_until = NULL WHERE id = :id",
                        [':id' => $admin['id']]
                    );
                }
            }
            
            // 验证密码
            if (!password_verify($password, $admin['password_hash'])) {
                // 密码错误，增加失败计数
                $this->handleLoginFailure($admin['id'], $admin['failed_attempts']);
                
                return [
                    'success' => false,
                    'error' => '用户名或密码错误',
                ];
            }
            
            // 登录成功，更新登录时间并重置失败计数
            $this->db->execute(
                "UPDATE administrators 
                 SET last_login_at = NOW(), failed_attempts = 0, locked_until = NULL 
                 WHERE id = :id",
                [':id' => $admin['id']]
            );
            
            // 设置会话
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['login_time'] = time();
            
            return [
                'success' => true,
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                ],
            ];
            
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => '系统错误，请稍后重试',
            ];
        }
    }
    
    /**
     * 处理登录失败
     */
    private function handleLoginFailure(int $adminId, int $failedAttempts): void
    {
        $security = $this->config['security'];
        $newAttempts = $failedAttempts + 1;
        
        if ($newAttempts >= $security['login_max_attempts']) {
            // 超过最大尝试次数，锁定账户
            $lockedUntil = date('Y-m-d H:i:s', time() + $security['login_lockout_time']);
            
            $this->db->execute(
                "UPDATE administrators 
                 SET failed_attempts = :attempts, locked_until = :locked_until 
                 WHERE id = :id",
                [
                    ':attempts' => $newAttempts,
                    ':locked_until' => $lockedUntil,
                    ':id' => $adminId,
                ]
            );
        } else {
            // 更新失败计数
            $this->db->execute(
                "UPDATE administrators 
                 SET failed_attempts = :attempts 
                 WHERE id = :id",
                [
                    ':attempts' => $newAttempts,
                    ':id' => $adminId,
                ]
            );
        }
    }
    
    /**
     * 登出
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->config['session']['name']);
            session_start();
        }
    }
    
    /**
     * 检查是否已登录
     */
    public function isAuthenticated(): bool
    {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }
        
        // 检查会话是否超时
        $loginTime = $_SESSION['login_time'] ?? 0;
        $lifetime = $this->config['session']['lifetime'];
        
        if (time() - $loginTime > $lifetime) {
            $this->logout();
            return false;
        }
        
        // 更新会话时间
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    /**
     * 获取当前管理员信息
     */
    public function getCurrentAdmin(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $adminId = $_SESSION['admin_id'];
        
        $admin = $this->db->fetchOne(
            "SELECT id, username, email, last_login_at FROM administrators WHERE id = :id",
            [':id' => $adminId]
        );
        
        return $admin;
    }
    
    /**
     * 要求登录（用于页面保护）
     */
    public function requireLogin(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /admin/login.php');
            exit;
        }
    }
}
