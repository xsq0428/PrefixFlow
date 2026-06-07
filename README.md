# 🔑 PrefixFlow · 卡密识别系统

[![GitHub Release](https://img.shields.io/github/v/release/xsq0428/PrefixFlow?style=flat-square)](https://github.com/xsq0428/PrefixFlow/releases)
[![License](https://img.shields.io/github/license/xsq0428/PrefixFlow?style=flat-square)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue?style=flat-square)](https://php.net)
[![PHP 8.2](https://img.shields.io/badge/PHP-8.2%20tested-purple?style=flat-square)](https://php.net)

**前缀驱动的智能卡密识别系统** — 通过最长前缀匹配算法，自动识别卡密归属产品并返回下载链接。

---

## ✨ 功能特性

### 🌐 用户端
- ✅ 公开查询页面，无需登录
- ✅ 输入卡密自动识别产品前缀
- ✅ 显示产品名称和下载链接
- ✅ 支持备用下载链接
- ✅ 响应式设计，兼容移动端

### 🛠️ 后台管理
- ✅ 管理员认证（会话管理）
- ✅ 登录失败锁定机制
- ✅ 产品管理（CRUD）
- ✅ 前缀规则管理（支持动态长度前缀）
- ✅ 最长前缀匹配算法
- ✅ 查询日志记录

## 技术栈

- **PHP**: 7.4+
- **MySQL**: 5.6+
- **Web 服务器**: Apache 2.4+ / Nginx 1.14+
- **浏览器**: 支持 HTML5 和 ES6 的现代浏览器

## 项目结构

```
.
├── api/                    # API 接口
│   ├── query.php          # 公共查询接口
│   └── admin/             # 后台管理 API
│       ├── login.php
│       ├── me.php
│       ├── products.php
│       └── prefixes.php
├── admin/                  # 后台管理页面
│   ├── login.php          # 登录页面
│   └── index.php          # 管理首页（产品 + 前缀管理）
├── public/                 # 公共页面
│   └── index.php          # 查询首页
├── includes/               # 核心类库
│   ├── Database.php       # 数据库连接类
│   ├── FileCache.php      # 文件缓存服务
│   ├── PrefixMatcher.php  # 前缀匹配服务
│   ├── ProductService.php # 产品管理服务
│   └── AuthService.php    # 认证服务
├── config/                 # 配置文件
│   └── config.php         # 系统配置
├── database/               # 数据库脚本
│   └── init.php          # 初始化脚本
├── cache/                  # 缓存目录
├── logs/                   # 日志目录
├── .env.example           # 环境变量示例
└── README.md              # 本文档
```

## 快速开始

### 1. 环境要求

- PHP 7.4+ 及以下扩展：
  - PDO
  - PDO_MySQL
  - OpenSSL
  - JSON
- MySQL 5.6+
- Web 服务器（Apache/Nginx）

### 2. 安装步骤

#### 2.1 克隆项目

```bash
cd /var/www/html
git clone <repository-url> license-system
cd license-system
```

#### 2.2 配置环境变量

复制环境变量示例文件并修改配置：

```bash
cp .env.example .env
```

编辑 `.env` 文件，设置数据库连接信息：

```bash
DB_HOST=localhost
DB_PORT=3306
DB_NAME=license_system
DB_USER=root
DB_PASSWORD=your_password_here
```

#### 2.3 初始化数据库

访问数据库初始化脚本：

```bash
# 方式 1：浏览器访问
http://your-domain/database/init.php

# 方式 2：命令行执行（需要配置 PHP CLI）
php database/init.php
```

初始化脚本会：
- 创建数据库
- 创建所有数据表
- 插入默认管理员账户

**默认管理员账户**：
- 用户名：`admin`
- 密码：`admin123`

⚠️ **首次登录后请立即修改密码！**

#### 2.4 配置 Web 服务器

**Apache 配置示例**：

```apache
<VirtualHost *:80>
    ServerName license.example.com
    DocumentRoot /var/www/html/license-system/public
    
    <Directory /var/www/html/license-system/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # 重定向 API 请求
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/api/
    RewriteRule ^(.*)$ /$1 [L]
</VirtualHost>
```

**Nginx 配置示例**：

```nginx
server {
    listen 80;
    server_name license.example.com;
    root /var/www/html/license-system/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
    }
    
    location /api {
        alias /var/www/html/license-system/api;
        
        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_index index.php;
        }
    }
    
    location ~ /\. {
        deny all;
    }
}
```

### 3. 验证安装

1. 访问首页：`http://your-domain/`
2. 输入测试卡密进行查询
3. 访问后台：`http://your-domain/admin/login.php`
4. 使用默认账户登录

## 使用指南

### 用户查询

1. 访问查询首页
2. 输入卡密（如：`ABC-1234-5678`）
3. 点击"查询"按钮
4. 系统显示产品名称和下载链接

### 后台管理

#### 添加产品

1. 登录后台管理系统
2. 点击"产品管理"标签页
3. 点击"+ 添加产品"按钮
4. 填写产品信息：
   - 产品名称（必填，唯一）
   - 描述（可选）
   - 下载链接（必填，URL 格式）
   - 备用下载链接（可选）
   - 状态（启用/禁用）
5. 点击"保存"

#### 添加前缀规则

1. 点击"前缀规则"标签页
2. 点击"+ 添加前缀规则"按钮
3. 配置规则：
   - 前缀字符串（如：`ABC`, `PROD`, `TRIAL`）
   - 关联产品（从下拉列表选择）
   - 优先级（数字越大优先级越高）
4. 点击"保存"

#### 前缀匹配规则

系统采用**最长前缀匹配**原则：

- 卡密 `ABC123` 匹配前缀 `ABC`
- 如果有多个前缀匹配（如 `AB` 和 `ABC`），优先匹配更长的前缀
- 前缀长度无限制，支持动态配置

## API 文档

### 公共接口

#### POST /api/query.php

查询卡密对应的产品信息

**请求参数**：
```json
{
    "key": "ABC-1234-5678"
}
```

**成功响应**（匹配到产品）：
```json
{
    "success": true,
    "data": {
        "matched": true,
        "prefix": "ABC",
        "product": {
            "id": 1,
            "name": "专业版软件",
            "download_url": "https://example.com/download/pro",
            "backup_url": "https://backup.example.com/download/pro"
        }
    },
    "message": "匹配成功",
    "response_time_ms": 45
}
```

**成功响应**（未匹配）：
```json
{
    "success": true,
    "data": {
        "matched": false,
        "prefix": null,
        "product": null
    },
    "message": "未找到匹配的产品，请检查卡密是否正确",
    "response_time_ms": 23
}
```

### 后台管理接口

需要登录后访问（通过 Session 认证）

#### POST /api/admin/login.php

管理员登录

**请求参数**：
```json
{
    "username": "admin",
    "password": "admin123"
}
```

#### GET /api/admin/products.php

获取产品列表

**查询参数**：
- `search`: 搜索关键词（可选）

#### POST /api/admin/products.php

创建产品

**请求参数**：
```json
{
    "name": "产品名称",
    "description": "产品描述",
    "download_url": "https://example.com/download",
    "backup_url": "https://backup.example.com/download",
    "status": 1
}
```

#### PUT /api/admin/products.php?id={id}

更新产品

#### DELETE /api/admin/products.php?id={id}

删除产品

**注意**：如果产品关联了前缀规则，则无法删除

#### GET /api/admin/prefixes.php

获取前缀规则列表

#### POST /api/admin/prefixes.php

创建前缀规则

**请求参数**：
```json
{
    "prefix": "ABC",
    "product_id": 1,
    "priority": 10
}
```

#### PUT /api/admin/prefixes.php?id={id}

更新前缀规则

#### DELETE /api/admin/prefixes.php?id={id}

删除前缀规则

## 数据库设计

### 表结构

- **products**: 产品表
- **prefix_rules**: 前缀规则表
- **administrators**: 管理员表
- **query_logs**: 查询日志表

详见 `.monkeycode/specs/license-key-recognition/design.md` 中的数据库设计部分。

## 安全特性

- ✅ SQL 注入防护（PDO 预处理语句）
- ✅ XSS 防护（输出转义）
- ✅ 密码加密存储（bcrypt）
- ✅ 登录失败锁定（5 次失败后锁定 15 分钟）
- ✅ 会话超时（30 分钟）
- ✅ 查询日志记录（卡密脱敏）

## 性能优化

- ✅ 文件缓存（避免频繁查询数据库）
- ✅ 缓存自动失效（5 分钟 TTL）
- ✅ 最长前缀匹配算法优化
- ✅ 数据库索引优化
- ✅ 查询响应时间 < 1 秒

## 故障排查

### 数据库连接失败

检查 `.env` 文件中的数据库配置是否正确：

```bash
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=license_system
```

### 缓存目录权限

确保 `cache/` 和 `logs/` 目录可写：

```bash
chmod -R 755 cache/ logs/
chown -R www-data:www-data cache/ logs/
```

### 查询结果为空

1. 检查数据库中是否存在前缀规则
2. 访问后台添加产品和前缀规则
3. 确认卡密前缀与配置的规则匹配

## 开发计划

- [ ] 添加查询统计功能
- [ ] 支持批量导入/导出
- [ ] 添加 API 密钥认证
- [ ] 支持多语言界面
- [ ] 添加邮件通知功能

## 许可证

MIT License

## 技术支持

如有问题，请提交 Issue 或联系开发团队。
