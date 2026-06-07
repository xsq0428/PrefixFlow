<?php
/**
 * 后台管理首页
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/AuthService.php';

$auth = new AuthService();
$auth->startSession();
$auth->requireLogin();

$admin = $auth->getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 卡密识别系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        /* 卡片式布局样式 */
        .card-list {
            display: grid;
            gap: 15px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .card-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .card-status.enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .card-status.disabled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .card-body {
            display: grid;
            gap: 12px;
        }
        
        .card-item {
            display: flex;
            gap: 10px;
        }
        
        .card-label {
            font-size: 12px;
            color: #666;
            min-width: 80px;
            flex-shrink: 0;
        }
        
        .card-value {
            font-size: 14px;
            color: #333;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .card-value a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .card-value a:hover {
            text-decoration: underline;
        }
        
        .card-footer {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        
        .card-footer button {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-edit-card {
            background: #4a90e2;
            color: white;
        }
        
        .btn-edit-card:hover {
            background: #357abd;
        }
        
        .btn-delete-card {
            background: #fff5f5;
            color: #e74c3c;
        }
        
        .btn-delete-card:hover {
            background: #e74c3c;
            color: white;
        }
        
        /* 桌面端隐藏卡片，显示表格 */
        .card-list {
            display: none;
        }
        
        .table-container {
            display: block;
        }
        
        /* 响应式导航栏 */
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex-wrap: wrap;
        }
        
        .navbar h1 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .navbar-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .navbar-user {
            font-size: 13px;
            color: #ecf0f1;
        }
        
        .navbar-links a {
            color: #ecf0f1;
            text-decoration: none;
            margin-left: 10px;
            font-size: 13px;
            padding: 5px 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        
        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .tabs {
            background: white;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            font-size: 15px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .tab-btn.active {
            color: #4a90e2;
            border-bottom-color: #4a90e2;
        }
        
        .tab-btn:hover:not(.active) {
            color: #333;
        }
        
        .tab-content {
            display: none;
            padding: 30px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .toolbar-actions button {
            padding: 0 20px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s;
            height: 42px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toolbar-actions button:hover {
            background: #357abd;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-box input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            width: 300px;
            height: 42px;
        }
        
        .search-box button {
            padding: 0 20px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            height: 42px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        td {
            font-size: 14px;
            color: #666;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.disabled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-btns button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .btn-edit {
            background: #4a90e2;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            color: #333;
            font-size: 20px;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }
        
        .modal-footer button {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-cancel {
            background: #e0e0e0;
            color: #333;
            border: none;
        }
        
        .btn-save {
            background: #4a90e2;
            color: white;
            border: none;
        }
        
        /* 移动端适配样式 */
        @media (max-width: 768px) {
            /* 隐藏表格，显示卡片 */
            .table-container {
                display: none;
            }
            
            .card-list {
                display: grid;
            }
            
            .navbar {
                padding: 10px 12px;
                flex-direction: column;
                gap: 10px;
            }
            
            .navbar h1 {
                font-size: 16px;
                width: 100%;
                text-align: center;
            }
            
            .navbar-info {
                width: 100%;
                justify-content: center;
                gap: 8px;
            }
            
            .navbar-user {
                font-size: 12px;
                order: 1;
            }
            
            .navbar-links {
                order: 2;
                display: flex;
                gap: 8px;
            }
            
            .navbar-links a {
                margin-left: 0;
                font-size: 12px;
                padding: 6px 12px;
            }
            
            .container {
                padding: 10px;
            }
            
            .tab-content {
                padding: 15px;
            }
            
            .toolbar {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
            
            .toolbar-actions {
                display: flex;
                justify-content: center;
            }
            
            .toolbar-actions button {
                width: 100%;
                padding: 12px 15px;
                font-size: 15px;
                height: 46px;
                line-height: 1.5;
            }
            
            .search-box {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-box input {
                width: 100%;
                font-size: 16px; /* 防止 iOS 缩放 */
                height: 46px;
            }
            
            .search-box button {
                width: 100%;
                padding: 12px;
                height: 46px;
            }
            
            /* 表格横向滚动 */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 8px;
            }
            
            table {
                min-width: 600px;
            }
            
            th, td {
                padding: 10px;
                font-size: 13px;
                white-space: nowrap;
            }
            
            /* 模态框适配 */
            .modal-content {
                padding: 20px;
                width: 95%;
                max-height: 90vh;
            }
            
            .modal-header h2 {
                font-size: 18px;
            }
            
            .modal-close {
                font-size: 28px;
                padding: 5px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 16px; /* 防止 iOS 缩放 */
                padding: 12px;
            }
            
            .modal-footer {
                flex-direction: column-reverse;
                gap: 10px;
            }
            
            .modal-footer button {
                width: 100%;
                padding: 12px;
                font-size: 15px;
            }
            
            .action-btns {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .action-btns button {
                width: 100%;
                padding: 8px;
                font-size: 13px;
            }
        }
        
        /* 小屏幕优化 */
        @media (max-width: 480px) {
            .navbar-user {
                display: none;
            }
            
            .tab-btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>后台管理系统</h1>
        <div class="navbar-info">
            <span class="navbar-user">欢迎，<?= htmlspecialchars($admin['username']) ?></span>
            <div class="navbar-links">
                <a href="/" target="_blank">查看首页</a>
                <a href="#" onclick="logout(); return false;">退出登录</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab('products')">产品管理</button>
                <button class="tab-btn" onclick="switchTab('prefixes')">前缀规则</button>
            </div>
            
            <div id="products" class="tab-content active">
                <div class="toolbar">
                    <div class="search-box">
                        <input type="text" id="productSearch" placeholder="搜索产品名称...">
                        <button onclick="searchProducts()">搜索</button>
                    </div>
                    <div class="toolbar-actions">
                        <button onclick="openProductModal()">+ 添加产品</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>产品名称</th>
                                <th>描述</th>
                                <th>下载链接</th>
                                <th>关联规则数</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <!-- 动态加载 -->
                        </tbody>
                    </table>
                </div>
                <!-- 卡片视图（移动端） -->
                <div class="card-list" id="productsCardList">
                    <!-- 动态加载卡片 -->
                </div>
            </div>
            
            <div id="prefixes" class="tab-content">
                <div class="toolbar">
                    <div class="toolbar-actions">
                        <button onclick="openPrefixModal()">+ 添加前缀规则</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>前缀</th>
                                <th>关联产品</th>
                                <th>优先级</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                    <tbody id="prefixesTableBody">
                        <!-- 动态加载 -->
                    </tbody>
                </table>
            </div>
            <!-- 卡片视图书（移动端） -->
            <div class="card-list" id="prefixesCardList">
                <!-- 动态加载卡片 -->
            </div>
        </div>
        </div>
    </div>
    
    <!-- 产品模态框 -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">添加产品</h2>
                <button class="modal-close" onclick="closeProductModal()">&times;</button>
            </div>
            <form id="productForm">
                <input type="hidden" id="productId">
                <div class="form-group">
                    <label>产品名称 *</label>
                    <input type="text" id="productName" required>
                </div>
                <div class="form-group">
                    <label>描述</label>
                    <textarea id="productDescription"></textarea>
                </div>
                <div class="form-group">
                    <label>下载链接 *</label>
                    <input type="url" id="productDownloadUrl" required>
                </div>
                <div class="form-group">
                    <label>备用下载链接</label>
                    <input type="url" id="productBackupUrl">
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select id="productStatus">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeProductModal()">取消</button>
                    <button type="submit" class="btn-save">保存</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 前缀规则模态框 -->
    <div id="prefixModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="prefixModalTitle">添加前缀规则</h2>
                <button class="modal-close" onclick="closePrefixModal()">&times;</button>
            </div>
            <form id="prefixForm">
                <input type="hidden" id="prefixId">
                <div class="form-group">
                    <label>前缀 *</label>
                    <input type="text" id="prefixValue" required placeholder="如：ABC, PROD, TRIAL">
                </div>
                <div class="form-group">
                    <label>关联产品 *</label>
                    <select id="prefixProduct" required>
                        <!-- 动态加载产品列表 -->
                    </select>
                </div>
                <div class="form-group">
                    <label>优先级</label>
                    <input type="number" id="prefixPriority" value="0" placeholder="数字越大优先级越高">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closePrefixModal()">取消</button>
                    <button type="submit" class="btn-save">保存</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        console.log('Script loaded');
        
        // 事件委托：处理卡片按钮点击
        document.addEventListener('click', function(e) {
            console.log('Click detected on:', e.target.tagName, e.target.className, e.target.dataset);
            
            // 使用 closest 查找最近的按钮元素
            const editProductBtn = e.target.closest('[data-action="edit-product"]');
            const deleteProductBtn = e.target.closest('[data-action="delete-product"]');
            const editProductTableBtn = e.target.closest('[class="btn-edit"][data-product-id]');
            const deleteProductTableBtn = e.target.closest('[class="btn-delete"][data-product-id]');
            const editPrefixBtn = e.target.closest('[data-action="edit-prefix"]');
            const deletePrefixBtn = e.target.closest('[data-action="delete-prefix"]');
            const editPrefixTableBtn = e.target.closest('[class="btn-edit"][data-prefix-id]');
            const deletePrefixTableBtn = e.target.closest('[class="btn-delete"][data-prefix-id]');
            
            // 编辑产品按钮（卡片）
            if (editProductBtn) {
                console.log('Edit product clicked, id:', editProductBtn.dataset.id);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(editProductBtn.dataset.id);
                openProductModal(id);
                return;
            }
            
            // 删除产品按钮（卡片）
            if (deleteProductBtn) {
                console.log('Delete product clicked, id:', deleteProductBtn.dataset.id);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(deleteProductBtn.dataset.id);
                deleteProduct(id);
                return;
            }
            
            // 编辑产品按钮（表格）
            if (editProductTableBtn) {
                console.log('Edit product (table) clicked, id:', editProductTableBtn.dataset.productId);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(editProductTableBtn.dataset.productId);
                openProductModal(id);
                return;
            }
            
            // 删除产品按钮（表格）
            if (deleteProductTableBtn) {
                console.log('Delete product (table) clicked, id:', deleteProductTableBtn.dataset.productId);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(deleteProductTableBtn.dataset.productId);
                deleteProduct(id);
                return;
            }
            
            // 编辑前缀按钮（卡片）
            if (editPrefixBtn) {
                console.log('Edit prefix clicked, id:', editPrefixBtn.dataset.id);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(editPrefixBtn.dataset.id);
                openPrefixModal(id);
                return;
            }
            
            // 删除前缀按钮（卡片）
            if (deletePrefixBtn) {
                console.log('Delete prefix clicked, id:', deletePrefixBtn.dataset.id);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(deletePrefixBtn.dataset.id);
                deletePrefix(id);
                return;
            }
            
            // 编辑前缀按钮（表格）
            if (editPrefixTableBtn) {
                console.log('Edit prefix (table) clicked, id:', editPrefixTableBtn.dataset.prefixId);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(editPrefixTableBtn.dataset.prefixId);
                openPrefixModal(id);
                return;
            }
            
            // 删除前缀按钮（表格）
            if (deletePrefixTableBtn) {
                console.log('Delete prefix (table) clicked, id:', deletePrefixTableBtn.dataset.prefixId);
                e.preventDefault();
                e.stopPropagation();
                const id = parseInt(deletePrefixTableBtn.dataset.prefixId);
                deletePrefix(id);
                return;
            }
        });
        
        console.log('Event listener registered');
        
        // Tab 切换
        function switchTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
            
            if (tabName === 'products') {
                loadProducts();
            } else if (tabName === 'prefixes') {
                loadPrefixes();
            }
        }
        
        // 加载产品列表
        async function loadProducts(search = '') {
            const response = await fetch(`/api/admin/products.php?search=${encodeURIComponent(search)}`);
            const data = await response.json();
            
            if (data.success) {
                const tbody = document.getElementById('productsTableBody');
                const cardList = document.getElementById('productsCardList');
                
                // 渲染表格视图
                tbody.innerHTML = data.data.map(product => `
                    <tr>
                        <td>${product.id}</td>
                        <td>${escapeHtml(product.name)}</td>
                        <td>${escapeHtml(product.description || '-')}</td>
                        <td><a href="${escapeHtml(product.download_url)}" target="_blank">下载链接</a></td>
                        <td>${product.rule_count}</td>
                        <td><span class="status-badge ${product.status == 1 ? 'enabled' : 'disabled'}">${product.status == 1 ? '启用' : '禁用'}</span></td>
                        <td class="action-btns">
                            <button class="btn-edit" data-product-id="${product.id}">编辑</button>
                            <button class="btn-delete" data-product-id="${product.id}">删除</button>
                        </td>
                    </tr>
                `).join('');
                
                // 渲染卡片视图
                cardList.innerHTML = data.data.map(product => `
                    <div class="card" data-product-id="${product.id}">
                        <div class="card-header">
                            <div class="card-title">${escapeHtml(product.name)}</div>
                            <span class="card-status ${product.status == 1 ? 'enabled' : 'disabled'}">${product.status == 1 ? '启用' : '禁用'}</span>
                        </div>
                        <div class="card-body">
                            <div class="card-item">
                                <span class="card-label">ID</span>
                                <span class="card-value">${product.id}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-label">描述</span>
                                <span class="card-value">${escapeHtml(product.description || '-')}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-label">下载链接</span>
                                <span class="card-value"><a href="${escapeHtml(product.download_url)}" target="_blank">点击下载</a></span>
                            </div>
                            <div class="card-item">
                                <span class="card-label">关联规则</span>
                                <span class="card-value">${product.rule_count} 个</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn-edit-card" data-action="edit-product" data-id="${product.id}">编辑</button>
                            <button class="btn-delete-card" data-action="delete-product" data-id="${product.id}">删除</button>
                        </div>
                    </div>
                `).join('');
            }
        }
        
        // 搜索产品
        function searchProducts() {
            const search = document.getElementById('productSearch').value;
            loadProducts(search);
        }
        
        // 打开产品模态框
        function openProductModal(id = null) {
            const modal = document.getElementById('productModal');
            if (!modal) {
                console.error('Modal element not found!');
                alert('错误：模态框元素未找到');
                return;
            }
            modal.classList.add('show');
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productModalTitle').textContent = id ? '编辑产品' : '添加产品';
            
            if (id) {
                loadProductForEdit(id);
            }
        }
        
        // 关闭产品模态框
        function closeProductModal() {
            document.getElementById('productModal').classList.remove('show');
        }
        
        // 加载产品进行编辑
        async function loadProductForEdit(id) {
            const response = await fetch(`/api/admin/products.php`);
            const data = await response.json();
            
            if (data.success) {
                const product = data.data.find(p => p.id == id);  // 使用 == 而不是 ===
                if (product) {
                    document.getElementById('productId').value = product.id;
                    document.getElementById('productName').value = product.name;
                    document.getElementById('productDescription').value = product.description || '';
                    document.getElementById('productDownloadUrl').value = product.download_url;
                    document.getElementById('productBackupUrl').value = product.backup_url || '';
                    document.getElementById('productStatus').value = product.status;
                }
            }
        }
        
        // 保存产品
        document.getElementById('productForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('productId').value;
            const data = {
                name: document.getElementById('productName').value,
                description: document.getElementById('productDescription').value,
                download_url: document.getElementById('productDownloadUrl').value,
                backup_url: document.getElementById('productBackupUrl').value,
                status: parseInt(document.getElementById('productStatus').value),
            };
            
            const method = id ? 'PUT' : 'POST';
            const url = id ? `/api/admin/products.php?id=${id}` : '/api/admin/products.php';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('保存成功');
                closeProductModal();
                loadProducts();
            } else {
                alert('保存失败：' + result.error);
            }
        });
        
        // 删除产品
        async function deleteProduct(id) {
            if (!confirm('确定要删除这个产品吗？如果有关联的前缀规则，将无法删除。')) {
                return;
            }
            
            const response = await fetch(`/api/admin/products.php?id=${id}`, { method: 'DELETE' });
            const result = await response.json();
            
            if (result.success) {
                alert('删除成功');
                loadProducts();
            } else {
                alert('删除失败：' + result.error);
            }
        }
        
        // 加载前缀规则列表
        async function loadPrefixes() {
            const response = await fetch('/api/admin/prefixes.php');
            const data = await response.json();
            
            if (data.success) {
                const tbody = document.getElementById('prefixesTableBody');
                const cardList = document.getElementById('prefixesCardList');
                
                // 渲染表格视图
                tbody.innerHTML = data.data.map(rule => `
                    <tr>
                        <td>${rule.id}</td>
                        <td><code>${escapeHtml(rule.prefix)}</code></td>
                        <td>${escapeHtml(rule.product_name || '已删除')}</td>
                        <td>${rule.priority}</td>
                        <td>${rule.created_at}</td>
                        <td class="action-btns">
                            <button class="btn-edit" data-prefix-id="${rule.id}">编辑</button>
                            <button class="btn-delete" data-prefix-id="${rule.id}">删除</button>
                        </td>
                    </tr>
                `).join('');
                
                // 渲染卡片视图
                cardList.innerHTML = data.data.map(rule => `
                    <div class="card" data-prefix-id="${rule.id}">
                        <div class="card-header">
                            <div class="card-title"><code>${escapeHtml(rule.prefix)}</code></div>
                            <span class="card-status enabled">优先级 ${rule.priority}</span>
                        </div>
                        <div class="card-body">
                            <div class="card-item">
                                <span class="card-label">ID</span>
                                <span class="card-value">${rule.id}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-label">关联产品</span>
                                <span class="card-value">${escapeHtml(rule.product_name || '已删除')}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-label">创建时间</span>
                                <span class="card-value">${rule.created_at}</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn-edit-card" data-action="edit-prefix" data-id="${rule.id}">编辑</button>
                            <button class="btn-delete-card" data-action="delete-prefix" data-id="${rule.id}">删除</button>
                        </div>
                    </div>
                `).join('');
                
                // 同时加载产品列表用于选择器
                loadProductsForSelect();
            }
        }
        
        // 加载产品选择器
        async function loadProductsForSelect() {
            const response = await fetch('/api/admin/products.php');
            const data = await response.json();
            
            if (data.success) {
                const select = document.getElementById('prefixProduct');
                select.innerHTML = data.data.map(p => `
                    <option value="${p.id}">${escapeHtml(p.name)}</option>
                `).join('');
            }
        }
        
        // 打开前缀模态框
        function openPrefixModal(id = null) {
            const modal = document.getElementById('prefixModal');
            if (!modal) {
                console.error('Prefix modal element not found!');
                return;
            }
            modal.classList.add('show');
            document.getElementById('prefixForm').reset();
            document.getElementById('prefixId').value = '';
            document.getElementById('prefixModalTitle').textContent = id ? '编辑前缀规则' : '添加前缀规则';
            
            if (id) {
                loadPrefixForEdit(id);
            }
        }
        
        // 关闭前缀模态框
        function closePrefixModal() {
            document.getElementById('prefixModal').classList.remove('show');
        }
        
        // 加载前缀规则进行编辑
        async function loadPrefixForEdit(id) {
            const response = await fetch('/api/admin/prefixes.php');
            const data = await response.json();
            
            if (data.success) {
                const rule = data.data.find(r => r.id == id);  // 使用 == 而不是 ===
                if (rule) {
                    document.getElementById('prefixId').value = rule.id;
                    document.getElementById('prefixValue').value = rule.prefix;
                    document.getElementById('prefixProduct').value = rule.product_id;
                    document.getElementById('prefixPriority').value = rule.priority;
                }
            }
        }
        
        // 保存前缀规则
        document.getElementById('prefixForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('prefixId').value;
            const data = {
                prefix: document.getElementById('prefixValue').value,
                product_id: parseInt(document.getElementById('prefixProduct').value),
                priority: parseInt(document.getElementById('prefixPriority').value),
            };
            
            const method = id ? 'PUT' : 'POST';
            const url = id ? `/api/admin/prefixes.php?id=${id}` : '/api/admin/prefixes.php';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('保存成功');
                closePrefixModal();
                loadPrefixes();
            } else {
                alert('保存失败：' + result.error);
            }
        });
        
        // 删除前缀规则
        async function deletePrefix(id) {
            if (!confirm('确定要删除这个前缀规则吗？')) {
                return;
            }
            
            const response = await fetch(`/api/admin/prefixes.php?id=${id}`, { method: 'DELETE' });
            const result = await response.json();
            
            if (result.success) {
                alert('删除成功');
                loadPrefixes();
            } else {
                alert('删除失败：' + result.error);
            }
        }
        
        // 退出登录
        function logout() {
            fetch('/api/admin/logout.php', { method: 'POST' })
                .then(() => {
                    window.location.href = '/admin/login.php';
                });
        }
        
        // HTML 转义
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 页面加载时初始化
        loadProducts();
    </script>
</body>
</html>
