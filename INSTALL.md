# 安装指南

本文档提供 PHP 卡密识别系统的详细安装步骤。

## 环境准备

### 必需软件

- **PHP**: 7.4 或更高版本
- **MySQL**: 5.6 或更高版本
- **Web 服务器**: Apache 2.4+ 或 Nginx 1.14+

### PHP 扩展检查

确保 PHP 已安装以下扩展：

```bash
php -m | grep -E "(pdo|pdo_mysql|openssl|json)"
```

必需扩展：
- `pdo`
- `pdo_mysql`
- `openssl`
- `json`

如果缺少扩展，请安装：

**Ubuntu/Debian**:
```bash
sudo apt-get install php7.4 php7.4-pdo php7.4-pdo-mysql php7.4-openssl php7.4-json
```

**CentOS/RHEL**:
```bash
sudo yum install php php-pdo php-mysql php-openssl php-json
```

### 配置 PHP

编辑 `php.ini` 确保以下设置：

```ini
; 时区设置
date.timezone = Asia/Shanghai

; 错误日志
log_errors = On
error_log = /var/log/php/error.log

; 文件上传（如需要）
file_uploads = On
upload_max_filesize = 10M
max_file_uploads = 5

; 会话设置
session.cookie_httponly = 1
session.cookie_secure = 0  ; 生产环境使用 HTTPS 时设置为 1
session.use_strict_mode = 1
```

## 数据库安装

### 创建数据库用户

推荐为系统创建专用的数据库用户：

```sql
CREATE DATABASE license_system 
DEFAULT CHARACTER SET utf8mb4 
DEFAULT COLLATE utf8mb4_general_ci;

CREATE USER 'license_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON license_system.* TO 'license_user'@'localhost';
FLUSH PRIVILEGES;
```

### 运行初始化脚本

**方式 1：浏览器访问**（推荐）

1. 将项目文件部署到 Web 服务器
2. 访问：`http://your-domain/database/init.php`
3. 等待初始化完成

**方式 2：命令行执行**

```bash
cd /path/to/project
php database/init.php
```

### 验证数据库

```sql
USE license_system;
SHOW TABLES;
```

应该看到以下表：
- administrators
- prefix_rules
- products
- query_logs

## 配置文件

### 环境变量

复制并编辑 `.env` 文件：

```bash
cp .env.example .env
nano .env
```

修改以下配置：

```bash
DB_HOST=localhost
DB_PORT=3306
DB_NAME=license_system
DB_USER=license_user
DB_PASSWORD=strong_password_here
```

### 目录权限

设置必需的目录权限：

```bash
cd /path/to/project

# 缓存目录
chmod 755 cache/
chown www-data:www-data cache/

# 日志目录
chmod 755 logs/
chown www-data:www-data logs/
```

## Web 服务器配置

### Apache 配置

创建新的虚拟主机配置：

```bash
sudo nano /etc/apache2/sites-available/license-system.conf
```

添加以下内容：

```apache
<VirtualHost *:80>
    ServerName license.example.com
    DocumentRoot /var/www/html/license-system/public
    
    <Directory /var/www/html/license-system/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory /var/www/html/license-system/api>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # 错误日志
    ErrorLog ${APACHE_LOG_DIR}/license-system-error.log
    CustomLog ${APACHE_LOG_DIR}/license-system-access.log combined
</VirtualHost>
```

启用站点并重启 Apache：

```bash
sudo a2ensite license-system
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx 配置

创建新的服务器块配置：

```bash
sudo nano /etc/nginx/sites-available/license-system
```

添加以下内容：

```nginx
server {
    listen 80;
    server_name license.example.com;
    
    root /var/www/html/license-system/public;
    index index.php index.html;
    
    # 访问日志
    access_log /var/log/nginx/license-system-access.log;
    error_log /var/log/nginx/license-system-error.log;
    
    # 首页和静态文件
    location / {
        try_files $uri $uri/ =404;
    }
    
    # PHP 处理 - 首页
    location ~ ^/index\.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
    }
    
    # API 目录
    location /api {
        alias /var/www/html/license-system/api;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_index index.php;
        }
    }
    
    # 后台管理
    location /admin {
        alias /var/www/html/license-system/admin;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_index index.php;
        }
    }
    
    # 数据库初始化脚本
    location /database {
        alias /var/www/html/license-system/database;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_index index.php;
        }
    }
    
    # 隐藏文件访问限制
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # 阻止访问敏感文件
    location ~ ^/(config|includes|cache|logs|\.monkeycode)/ {
        deny all;
        return 404;
    }
}
```

启用配置并重启 Nginx：

```bash
sudo ln -s /etc/nginx/sites-available/license-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 验证安装

### 1. 访问首页

```
http://your-domain/
```

应该看到查询页面。

### 2. 访问后台登录

```
http://your-domain/admin/login.php
```

使用默认凭据登录：
- 用户名：`admin`
- 密码：`admin123`

### 3. 测试查询功能

1. 登录后台
2. 添加一个产品
3. 添加一个前缀规则
4. 返回首页，输入测试卡密进行查询

### 4. 检查日志

```bash
tail -f /var/log/nginx/license-system-error.log
# 或
tail -f /var/log/apache2/license-system-error.log
```

## 安全加固

### 修改默认密码

**重要**：首次登录后立即修改管理员密码！

### HTTPS 配置（生产环境必需）

**使用 Let's Encrypt 获取免费 SSL 证书**：

```bash
sudo apt-get install certbot python3-certbot-nginx

# Nginx
sudo certbot --nginx -d license.example.com

# Apache
sudo certbot --apache -d license.example.com
```

### 防火墙配置

```bash
# Ubuntu UFW
sudo ufw allow 'Nginx HTTP'
sudo ufw allow 'Nginx HTTPS'
sudo ufw enable

# CentOS firewalld
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 数据库安全

```sql
-- 限制数据库用户权限（如果只需要查询）
REVOKE CREATE, DROP, ALTER ON license_system.* FROM 'license_user'@'localhost';
FLUSH PRIVILEGES;
```

## 常见问题

### 问题 1: 数据库连接失败

**错误信息**: `Database connection failed`

**解决方案**:
1. 检查 `.env` 文件配置
2. 确认 MySQL 服务正在运行：`sudo systemctl status mysql`
3. 验证数据库用户权限

### 问题 2: 权限错误

**错误信息**: `Permission denied` 或 `Cannot write to cache directory`

**解决方案**:
```bash
sudo chown -R www-data:www-data /path/to/project/cache
sudo chown -R www-data:www-data /path/to/project/logs
sudo chmod -R 755 /path/to/project/cache
sudo chmod -R 755 /path/to/project/logs
```

### 问题 3: 404 错误

**解决方案**:
1. 检查 Web 服务器配置的 `DocumentRoot` 是否正确
2. 确认 `.htaccess` 文件存在（Apache）
3. 检查 Nginx 的 `location` 配置

### 问题 4: PHP 版本过低

**错误信息**: 语法错误或类未找到

**解决方案**:
```bash
php -v  # 检查当前版本

# Ubuntu 升级到 PHP 7.4
sudo apt-get install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.4 php7.4-pdo php7.4-pdo-mysql
```

## 下一步

安装完成后，请参考：
- [README.md](README.md) - 使用指南
- [DEPLOY.md](DEPLOY.md) - 部署指南

## 技术支持

如有问题，请查看系统日志或联系技术支持团队。
