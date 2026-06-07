# 宝塔面板部署教程

本教程适用于已在服务器上安装好宝塔面板（7.x+ 版本）的用户，指导如何快速部署 PHP 卡密识别系统。

## 环境要求

- **宝塔面板**: 7.0 或更高版本
- **PHP**: 7.4 或更高版本
- **MySQL**: 5.6 或更高版本
- **系统**: CentOS 7+/Ubuntu 18.04+/Debian 9+

---

## 第一步：上传项目文件

### 1.1 进入网站目录

1. 登录宝塔面板
2. 点击左侧菜单 **文件**
3. 进入网站目录（默认为 `/www/wwwroot/`）

### 1.2 上传项目

**方式一：在线上传**

1. 点击宝塔界面右上角 **上传** 按钮
2. 选择项目压缩包（建议先打包成 zip）
3. 上传完成后右键点击 **解压**

**方式二：通过 FTP 上传**

1. 使用 FTP 工具（如 FileZilla、Xshell）
2. 连接到服务器（主机 IP、FTP 用户名、密码）
3. 将整个项目上传到 `/www/wwwroot/license-system/`

### 1.3 目录结构确认

上传后目录结构应如下：

```
/www/wwwroot/license-system/
├── api/
├── admin/
├── public/
├── includes/
├── config/
├── database/
├── cache/
├── logs/
├── test/
├── .env.example
├── README.md
└── INSTALL.md
```

---

## 第二步：创建网站

### 2.1 添加网站

1. 点击宝塔左侧菜单 **网站**
2. 点击 **添加站点** 按钮
3. 填写以下信息：

| 配置项 | 填写内容 |
|--------|---------|
| 域名 | 填写你的域名（如 `license.example.com`）或服务器 IP |
| 根目录 | 选择 `/www/wwwroot/license-system/public` |
| PHP 版本 | 选择 **PHP 7.4** 或更高 |
| 数据库 | 选择 **MySQL** |

### 2.2 数据库配置

在添加网站时，宝塔会自动创建数据库：

| 配置项 | 说明 |
|--------|------|
| 数据库用户名 | 建议使用默认生成的 |
| 数据库密码 | 生成强密码并记录 |
| 数据库名 | 自动命名（如 `license_system`） |

**重要**：创建完成后，**立即复制数据库信息保存**（后续需要配置到 .env 文件）

### 2.3 SSL 证书（可选）

1. 在网站列表中找到刚创建的网站
2. 点击 **设置**
3. 点击左侧 **SSL** 标签
4. 选择 **Let's Encrypt** 免费证书
5. 勾选域名后点击 **申请**

---

## 第三步：配置 PHP 环境

### 3.1 安装 PHP 扩展

1. 点击宝塔左侧菜单 **软件商店**
2. 找到 **PHP 7.4**（或你安装的版本）
3. 点击 **设置**
4. 点击左侧 **安装扩展**
5. 确保安装以下扩展：

```
pdo_mysql
openssl
mbstring
fileinfo
zip
```

### 3.2 调整 PHP 配置

在 PHP 设置界面，点击 **配置修改**：

```ini
; 时区设置
date.timezone = Asia/Shanghai

; 会话安全
session.cookie_httponly = 1
session.use_strict_mode = 1

; 文件上传限制
upload_max_filesize = 10M
max_file_uploads = 5

; 错误日志
log_errors = On
display_errors = Off  ; 生产环境必须关闭
```

点击 **保存** 并 **重启 PHP**

---

## 第四步：配置项目

### 4.1 创建 .env 文件

1. 在宝塔文件浏览器中，进入项目目录 `/www/wwwroot/license-system/`
2. 复制 `.env.example` 为 `.env`
3. 右键点击 `.env` 选择 **编辑**

### 4.2 修改数据库配置

编辑 `.env` 文件，填入第二步记录的数据库信息：

```bash
# 数据库配置
DB_HOST=localhost
DB_PORT=3306
DB_NAME=license_system        # 填写宝塔创建的数据库名
DB_USER=license_user          # 填写宝塔创建的数据库用户名
DB_PASSWORD=your_password     # 填写宝塔生成的数据库密码

# 应用配置
APP_DEBUG=false

# 日志级别
LOG_LEVEL=INFO
```

**保存** 文件

### 4.3 设置目录权限

在宝塔文件浏览器中：

1. 选中 `cache` 文件夹，右键 **权限**
2. 设置权限为 **755** 或 **777**
3. 所有者选择 **www**
4. 同样设置 `logs` 文件夹

---

## 第五步：网站伪静态配置

### 5.1 配置 Nginx

1. 回到宝塔 **网站** 页面
2. 找到你的网站，点击 **设置**
3. 点击左侧 **伪静态**
4. 选择 **thinkphp** 或 **empty**（本项目不需要复杂伪静态）
5. 或者手动添加以下内容：

```nginx
location /api {
    alias /www/wwwroot/license-system/api;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/tmp/php-cgi-74.sock;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_index index.php;
    }
}

location /admin {
    alias /www/wwwroot/license-system/admin;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/tmp/php-cgi-74.sock;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_index index.php;
    }
}

location ~ /\.(htaccess|git|svn) {
    deny all;
}

location ~* \.(sql|log|md)$ {
    deny all;
}
```

### 5.2 Apache 配置

如果使用 Apache，在 **伪静态** 页面选择 **typecho** 或直接使用项目自带的 `.htaccess` 文件。

---

## 第六步：初始化数据库

### 6.1 访问初始化脚本

1. 打开浏览器
2. 访问：`http://your-domain/database/init.php`

例如：
- `http://123.45.67.89/database/init.php`
- `https://license.example.com/database/init.php`

### 6.2 确认初始化成功

页面应显示类似内容：

```
数据库初始化
✓ 数据库创建成功
✓ products 表创建成功
✓ prefix_rules 表创建成功
✓ administrators 表创建成功
✓ query_logs 表创建成功
✓ 默认管理员账户创建成功（用户名：admin，密码：admin123）

初始化完成！
```

### 6.3 删除初始化脚本（重要）

为安全起见，初始化完成后删除该文件：

1. 在宝塔文件浏览器中进入 `/www/wwwroot/license-system/database/`
2. 右键点击 `init.php` 选择 **删除**

---

## 第七步：验证安装

### 7.1 测试公共查询页面

1. 访问首页：`http://your-domain/`
2. 应显示卡密查询页面

### 7.2 测试后台登录

1. 访问后台：`http://your-domain/admin/login.php`
2. 使用默认凭据登录：
   - 用户名：`admin`
   - 密码：`admin123`

### 7.3 常见问题排查

**问题 1：404 错误**

- 检查网站根目录是否设置为 `/www/wwwroot/license-system/public`
- 检查伪静态配置

**问题 2：500 错误**

- 查看网站错误日志（宝塔网站设置 → 日志）
- 检查 `.env` 文件数据库配置是否正确

**问题 3：数据库连接失败**

- 确认 MySQL 服务已启动（宝塔软件商店 → MySQL → 启动）
- 检查数据库用户名密码是否正确

**问题 4：权限错误**

- 检查 `cache/` 和 `logs/` 目录权限
- 所有者设置为 `www`，权限 755 或 777

---

## 第八步：配置后台管理

### 8.1 添加产品

1. 登录后台管理系统
2. 进入 **产品管理** 标签页
3. 点击 **+ 添加产品**

填写示例：
- 产品名称：`专业版软件`
- 描述：`专业版软件下载`
- 下载链接：`https://example.com/download/pro.zip`
- 备用链接：（可选）
- 状态：启用

### 8.2 添加前缀规则

1. 进入 **前缀规则** 标签页
2. 点击 **+ 添加前缀规则**

示例配置：
- 前缀：`PRO-` 或 `PROFESSIONAL-`（前缀长度不限）
- 关联产品：选择刚创建的产品
- 优先级：10（数字越大优先级越高）

### 8.3 测试查询

1. 返回首页 `http://your-domain/`
2. 输入测试卡密，如：`PRO-1234-5678`
3. 点击查询
4. 应显示产品名称和下载链接

---

## 第九步：安全加固

### 9.1 修改管理员密码

**重要**：首次登录后立即修改默认密码！

1. 登录后修改（如后台有密码修改功能）
2. 或通过数据库修改：

```sql
UPDATE administrators 
SET password_hash = '$2y$12$...'  -- 使用 password_hash() 生成新密码
WHERE username = 'admin';
```

或使用 PHP 生成新密码哈希：
```php
echo password_hash('your_new_password', PASSWORD_BCRYPT, ['cost' => 12]);
```

### 9.2 防火墙配置

1. 点击宝塔左侧 **安全** 菜单
2. 只开放必要的端口：
   - `80`（HTTP）
   - `443`（HTTPS，如果启用 SSL）
   - `3306`（MySQL，仅建议内部访问）

### 9.3 禁止敏感目录访问

在 Nginx 配置中添加：

```nginx
location ~ ^/(config|includes|cache|logs|\.monkeycode)/ {
    deny all;
    return 404;
}
```

### 9.4 开启防跨站攻击

1. 宝塔网站设置 → 默认文档
2. 添加安全配置

---

## 第十步：备份与维护

### 10.1 配置自动备份

1. 点击宝塔左侧 **设置** → **备份设置**
2. 配置数据库自动备份（建议每天备份）
3. 配置网站文件备份

### 10.2 日志管理

- 网站访问日志：宝塔网站设置 → 日志
- 系统日志：`/www/wwwroot/license-system/logs/`
- PHP 错误日志：宝塔 PHP 设置 → 错误日志

建议定期清理日志文件，避免占用过多空间。

### 10.3 性能监控

1. 使用宝塔 **监控** 功能查看服务器负载
2. 检查数据库连接数
3. 查看磁盘空间使用情况

---

## 快速部署命令（SSH 方式）

如果熟悉 SSH 操作，可以通过命令快速部署：

```bash
# 1. 进入项目目录
cd /www/wwwroot/license-system

# 2. 上传项目（如果有压缩包）
# 上传后使用 unzip 解压
unzip project.zip

# 3. 配置 .env 文件
cp .env.example .env
nano .env  # 编辑数据库信息

# 4. 设置权限
chmod -R 755 cache/ logs/
chown -R www:www cache/ logs/

# 5. 导入数据库配置
# 通过宝塔创建数据库后，在项目目录执行
php database/init.php
```

---

## 附录：数据库手动创建

如果添加网站时未创建数据库，可以手动创建：

### 方式一：通过宝塔

1. 点击左侧 **数据库**
2. 点击 **添加数据库**
3. 填写数据库名、用户名、密码
4. 访问权限选择 **本地服务器** 或 **所有人**

### 方式二：通过命令行

```sql
CREATE DATABASE license_system 
DEFAULT CHARACTER SET utf8mb4 
DEFAULT COLLATE utf8mb4_general_ci;

GRANT ALL PRIVILEGES ON license_system.* 
TO 'license_user'@'localhost' 
IDENTIFIED BY 'strong_password';

FLUSH PRIVILEGES;
```

---

## 附录：常用宝塔命令

```bash
# 重启 Nginx
/etc/init.d/nginx restart

# 重启 Apache
/etc/init.d/httpd restart

# 重启 PHP
/etc/init.d/php-fpm-74 restart

# 查看 Nginx 日志
tail -f /www/wwwlogs/your-domain.log

# 查看 PHP 错误日志
tail -f /tmp/php-fpm.log
```

---

## 常见问题

### Q: 宝塔面板登录不进去？

A: 检查防火墙是否开放了面板端口（默认 8888），在宝塔安全页面添加入站规则。

### Q: 访问网站显示 502 Bad Gateway？

A: PHP 服务未启动，在宝塔软件商店启动 PHP-FPM。

### Q: 查询页面一直加载中？

A: 检查数据库连接配置，查看 `.env` 文件是否正确配置数据库信息。

### Q: 如何查看系统实际占用的资源？

A: 使用宝塔监控功能，或 SSH 执行 `htop`、`df -h` 等命令。

---

## 技术支持

如遇到文档未涵盖的问题：

1. 查看宝塔任务列表中的失败任务
2. 检查服务器资源使用情况（CPU、内存、磁盘）
3. 查阅项目 ISSUE 或联系技术支持

祝部署顺利！🎉
