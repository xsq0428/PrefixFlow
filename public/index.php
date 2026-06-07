<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>卡密识别系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .result {
            margin-top: 25px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        
        .result.success {
            background: #f0fff4;
            border: 1px solid #c6f6d5;
            display: block;
        }
        
        .result.error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            display: block;
        }
        
        .result h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .product-name {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .download-links {
            margin-top: 15px;
        }
        
        .download-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 10px;
            transition: background 0.3s;
        }
        
        .download-btn:hover {
            background: #5568d3;
        }
        
        .download-btn.backup {
            background: #808080;
        }
        
        .download-btn.backup:hover {
            background: #666;
        }
        
        .loading {
            text-align: center;
            display: none;
        }
        
        .loading::after {
            content: '';
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid #f3f3f3;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 12px;
        }
        
        .admin-link {
            color: #999;
            text-decoration: none;
        }
        
        .admin-link:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>卡密识别系统</h1>
            <p>输入您的卡密，快速获取产品下载链接</p>
        </div>
        
        <form id="queryForm">
            <div class="form-group">
                <label for="keyInput">卡密</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="keyInput" 
                    placeholder="请输入卡密（如：ABC-1234-5678）"
                    autocomplete="off"
                    required
                >
            </div>
            
            <button type="submit" class="btn" id="submitBtn">查询</button>
        </form>
        
        <div class="loading" id="loading"></div>
        
        <div class="result" id="result">
            <h3 id="resultTitle"></h3>
            <div id="resultContent"></div>
        </div>
        
        <div class="footer">
            <a href="/admin/login.php" class="admin-link">管理后台</a>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('queryForm');
        const keyInput = document.getElementById('keyInput');
        const submitBtn = document.getElementById('submitBtn');
        const loading = document.getElementById('loading');
        const result = document.getElementById('result');
        const resultTitle = document.getElementById('resultTitle');
        const resultContent = document.getElementById('resultContent');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const key = keyInput.value.trim();
            if (!key) {
                showResult('error', '请输入卡密');
                return;
            }
            
            // 显示加载状态
            submitBtn.disabled = true;
            loading.style.display = 'block';
            result.className = 'result';
            
            try {
                const response = await fetch('/api/query.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ key }),
                });
                
                const data = await response.json();
                
                if (data.success && data.data.matched) {
                    const product = data.data.product;
                    let html = `<div class="product-name">${escapeHtml(product.name)}</div>`;
                    html += '<div class="download-links">';
                    html += `<a href="${escapeHtml(product.download_url)}" class="download-btn" target="_blank">下载</a>`;
                    
                    if (product.backup_url) {
                        html += `<a href="${escapeHtml(product.backup_url)}" class="download-btn backup" target="_blank">备用下载</a>`;
                    }
                    
                    html += '</div>';
                    
                    showResult('success', '匹配成功', html);
                } else {
                    showResult('error', data.message || '未找到匹配的产品');
                }
            } catch (error) {
                console.error('Query error:', error);
                showResult('error', '查询失败，请稍后重试');
            } finally {
                submitBtn.disabled = false;
                loading.style.display = 'none';
            }
        });
        
        function showResult(type, title, content = '') {
            result.className = 'result ' + type;
            resultTitle.textContent = title;
            resultContent.innerHTML = content;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 支持回车键查询
        keyInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                form.dispatchEvent(new Event('submit'));
            }
        });
        
        // 页面加载后自动聚焦输入框
        window.addEventListener('load', function() {
            keyInput.focus();
        });
    </script>
</body>
</html>
