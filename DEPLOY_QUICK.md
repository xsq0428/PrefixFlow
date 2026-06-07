# 宝塔快速部署教程

## 1. 上传项目

1. 登录宝塔面板 → **文件**
2. 进入 `/www/wwwroot/`
3. 上传项目压缩包并解压，重命名为 `license-system`

## 2. 添加网站

1. **网站** → **添加站点**
2. 域名：填你的域名或 IP
3. 根目录：选择 `/www/wwwroot/license-system/public`
4. PHP 版本：选 **PHP 7.4+**
5. 数据库：选择 MySQL，记录数据库名、用户名、密码

## 3. 配置项目

### 3.1 创建 .env 文件

进入 `/www/wwwroot/license-system/`，复制 `.env.example` 为 `.env`，编辑填入数据库信息：

```bash
DB_HOST=localhost
DB_PORT=3306
DB_NAME=数据库名
DB_USER=数据库用户名
DB_PASSWORD=数据库密码
```

### 3.2 设置权限

在 `cache/` 和 `logs/` 目录上右键 → **权限** → 所有者选 `www`，权限选 `755`

### 3.3 导入数据库

1. **数据库** → 找到刚创建的数据库 → **管理**
2. 进入 phpMyAdmin → **SQL** 标签
3. 打开 `/workspace/database/init.sql` 文件内容，粘贴进去 → **执行**

### 3.4 初始化并完成

1. 浏览器访问：`http://你的域名/database/init.php`
2. 看到「初始化完成」后，**删除** `database/init.php` 文件

## 4. 验证

| 页面 | 地址 |
|------|------|
| 查询首页 | `http://你的域名/` |
| 后台登录 | `http://你的域名/admin/login.php` |

默认管理员：`admin` / `admin123`

首次登录后请立即修改密码。
