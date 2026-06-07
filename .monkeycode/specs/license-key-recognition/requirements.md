# Requirements Document

## Introduction

本系统为 PHP 卡密识别系统，允许用户通过输入卡密前缀来识别对应的产品名称与下载链接。系统提供公开查询页面（无需登录）和后台管理界面（需认证），支持动态配置卡密前缀规则、产品管理和下载链接管理。

## Glossary

- **卡密（License Key）**：用户输入的产品识别码，由前缀和后续字符组成
- **卡密前缀（Key Prefix）**：卡密的起始部分，用于匹配产品规则，长度动态可变
- **产品（Product）**：系统中注册的软件产品，包含名称和下载链接
- **前缀规则（Prefix Rule）**：卡密前缀与产品的映射关系配置
- **管理员（Administrator）**：拥有后台管理权限的用户
- **查询用户（Query User）**：使用公开查询页面的最终用户

## Requirements

### 需求 1：用户自助查询功能

**用户故事：** AS 查询用户，I WANT 通过输入卡密自动识别产品信息，SO THAT 我可以快速找到对应产品的下载链接而无需人工咨询

#### Acceptance Criteria

1. WHEN 查询用户在公开查询页面输入卡密并提交，系统 SHALL 提取卡密前缀并匹配对应的产品规则
2. WHEN 系统成功匹配到卡密前缀规则，系统 SHALL 显示产品名称和下载链接
3. WHEN 系统未匹配到任何卡密前缀规则，系统 SHALL 显示友好的错误提示信息
4. WHILE 查询用户访问查询页面，系统 SHALL 无需用户登录或认证
5. WHEN 查询用户输入空卡密或无效格式，系统 SHALL 提示用户输入有效的卡密
6. WHEN 系统匹配到多个可能的前缀规则（前缀重叠），系统 SHALL 采用最长前缀匹配原则

### 需求 2：后台管理认证功能

**用户故事：** AS 管理员，I WANT 通过用户名和密码登录后台管理系统，SO THAT 我可以安全地配置卡密前缀规则和管理产品信息

#### Acceptance Criteria

1. WHEN 管理员访问后台管理页面，系统 SHALL 要求管理员进行身份认证
2. WHEN 管理员输入正确的用户名和密码，系统 SHALL  granting 后台管理界面访问权限
3. WHEN 管理员输入错误的用户名或密码，系统 SHALL 显示认证失败提示信息
4. WHILE 管理员已登录，系统 SHALL 保持会话状态直到管理员主动登出或会话超时
5. WHEN 管理员会话超时，系统 SHALL 自动跳转到登录页面并要求重新认证
6. IF 管理员连续 5 次输入错误密码，系统 SHALL 临时锁定该账户 15 分钟

### 需求 3：卡密前缀规则管理功能

**用户故事：** AS 管理员，I WANT 在后台界面添加、修改和删除卡密前缀规则，SO THAT 我可以根据产品更新动态调整卡密识别规则

#### Acceptance Criteria

1. WHEN 管理员在后台界面添加新的前缀规则，系统 SHALL 允许管理员配置前缀字符串、关联产品和下载链接
2. WHEN 管理员修改现有前缀规则，系统 SHALL 更新前缀配置并立即生效
3. WHEN 管理员删除前缀规则，系统 SHALL 移除该规则并确认删除操作
4. WHILE 管理员添加前缀规则，系统 SHALL 验证前缀字符串的唯一性（不允许重复前缀）
5. WHEN 系统检测到新前缀与现有前缀存在包含关系，系统 SHALL 提示管理员确认是否存在冲突
6. IF 管理员尝试添加空前缀或非法字符前缀，系统 SHALL 拒绝并提示输入有效的前缀字符串
7. WHEN 管理员查看前缀规则列表，系统 SHALL 按创建时间倒序显示所有规则

### 需求 4：产品管理功能

**用户故事：** AS 管理员，I WANT 在后台界面添加、修改和删除产品信息，SO THAT 我可以维护准确的产品目录和下载链接

#### Acceptance Criteria

1. WHEN 管理员添加新产品，系统 SHALL 允许管理员配置产品名称、描述和下载链接
2. WHEN 管理员修改产品信息，系统 SHALL 更新产品配置并立即生效
3. WHEN 管理员删除产品，系统 SHALL 检查是否存在关联的前缀规则并提示确认
4. IF 产品存在关联的前缀规则，系统 SHALL 禁止删除或要求先解除关联
5. WHILE 管理员管理产品列表，系统 SHALL 显示每个产品的关联前缀规则数量
6. WHEN 管理员查看产品列表，系统 SHALL 支持按产品名称搜索和筛选
7. IF 管理员尝试添加重复产品名称，系统 SHALL 提示产品名称已存在

### 需求 5：下载链接管理功能

**用户故事：** AS 管理员，I WANT 为每个产品配置一个或多个下载链接，SO THAT 查询用户可以获得正确的产品下载地址

#### Acceptance Criteria

1. WHEN 管理员配置产品下载链接，系统 SHALL 验证下载链接的 URL 格式有效性
2. WHEN 管理员添加多个下载链接（主链接和备用链接），系统 SHALL 按优先级排序显示
3. WHILE 查询用户访问下载链接，系统 SHALL 直接跳转到对应的下载地址
4. IF 下载链接格式无效（非 HTTP/HTTPS URL），系统 SHALL 拒绝保存并提示修正
5. WHEN 主下载链接失效，系统 SHALL 允许管理员快速切换到备用链接

### 需求 6：数据库持久化功能

**用户故事：** AS 系统，I WANT 将卡密前缀规则、产品信息和管理员账户存储在数据库中，SO THAT 数据可以持久化并支持动态查询

#### Acceptance Criteria

1. WHEN 系统启动，系统 SHALL 从数据库加载所有卡密前缀规则和产品数据到内存缓存
2. WHEN 管理员修改前缀规则或产品信息，系统 SHALL 实时更新数据库并刷新缓存
3. WHILE 查询用户发起查询请求，系统 SHALL 从缓存中快速匹配前缀规则
4. IF 数据库连接失败，系统 SHALL 记录错误日志并显示系统维护提示
5. WHEN 系统执行批量操作（如导入导出），系统 SHALL 使用事务保证数据一致性

### 需求 7：API 接口服务功能

**用户故事：** AS 前端界面，I WANT 通过 RESTful API 与后端交互，SO THAT 我可以实现前后端分离的架构并支持未来扩展

#### Acceptance Criteria

1. WHEN 查询页面提交卡密查询，前端 SHALL 调用 POST /api/query 接口获取识别结果
2. WHEN 管理员登录后台，前端 SHALL 调用 POST /api/admin/login 接口进行认证
3. WHEN 管理员管理前缀规则，前端 SHALL 调用 GET/POST/PUT/DELETE /api/admin/prefixes 接口
4. WHEN 管理员管理产品，前端 SHALL 调用 GET/POST/PUT/DELETE /api/admin/products 接口
5. IF API 请求失败，后端 SHALL 返回标准 JSON 格式的错误响应包含错误代码和消息
6. WHILE API 处理请求，后端 SHALL 对所有输入参数进行验证和过滤防止注入攻击
7. WHEN 管理员操作成功，后端 SHALL 返回统一格式的 JSON 响应包含操作结果

### 需求 8：系统安全与防护功能

**用户故事：** AS 系统，I WANT 实现基本的安全防护措施，SO THAT 系统可以抵御常见攻击并保护数据安全

#### Acceptance Criteria

1. WHEN 系统接收用户输入，系统 SHALL 对所有输入进行 XSS 过滤和 SQL 注入防护
2. WHILE 管理员密码存储，系统 SHALL 使用 bcrypt 或 Argon2 进行哈希加密
3. WHEN 系统返回错误信息，系统 SHALL 避免泄露敏感信息（如数据库结构、路径等）
4. IF 系统检测到异常请求频率（如每秒超过 100 次查询），系统 SHALL 临时限制该 IP 的访问
5. WHILE 管理员会话，系统 SHALL 使用 HTTPS 传输加密（生产环境）

### 需求 9：系统性能与可用性功能

**用户故事：** AS 查询用户，I WANT 系统快速响应查询请求，SO THAT 我可以在 1 秒内获得识别结果

#### Acceptance Criteria

1. WHEN 查询用户提交卡密，系统 SHALL 在 1 秒内返回识别结果
2. WHILE 系统正常运行，系统 SHALL 支持至少 100 并发查询请求
3. WHEN 数据库或缓存更新，系统 SHALL 保证查询结果的一致性（无脏读）
4. IF 系统服务重启，系统 SHALL 在 30 秒内恢复服务并加载缓存
5. WHILE 系统运行，系统 SHALL 记录所有查询操作日志用于审计和分析
