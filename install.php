<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>耀星系列查询 - 安装</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .install-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 560px;
            width: 100%;
        }
        
        .install-box h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .install-box .subtitle {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: flex;
            gap: 12px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
            font-size: 14px;
        }
        
        .btn-install {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-install:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-install:disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .result.success {
            background: #f0fff4;
            border: 1px solid #c6f6d5;
            color: #22543d;
            display: block;
        }
        
        .result.error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: #9b2c2c;
            display: block;
        }
        
        .result a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .result a:hover {
            text-decoration: underline;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            vertical-align: middle;
            margin-left: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .info-box {
            background: #edf2f7;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .info-box strong {
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="install-box">
        <h1>耀星系列查询</h1>
        <p class="subtitle">系统安装向导</p>
        
        <div class="info-box">
            <strong>提示：</strong>请先在宝塔面板中创建数据库，然后填写以下配置信息。
        </div>
        
        <form id="installForm">
            <div class="form-group">
                <label for="dbName">数据库名称</label>
                <input type="text" id="dbName" placeholder="如：license_system" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="dbHost">数据库地址</label>
                    <input type="text" id="dbHost" value="localhost" placeholder="localhost">
                </div>
                <div class="form-group">
                    <label for="dbPort">端口</label>
                    <input type="number" id="dbPort" value="3306" placeholder="3306">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="dbUser">数据库用户名</label>
                    <input type="text" id="dbUser" placeholder="如：root 或 bt_user" required>
                </div>
                <div class="form-group">
                    <label for="dbPassword">数据库密码</label>
                    <input type="text" id="dbPassword" placeholder="数据库密码" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="adminUsername">管理员用户名</label>
                <input type="text" id="adminUsername" value="admin" placeholder="管理员用户名">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="adminPassword">管理员密码</label>
                    <input type="text" id="adminPassword" value="admin123" placeholder="默认 admin123">
                </div>
                <div class="form-group">
                    <label for="installSample">示例数据</label>
                    <select id="installSample">
                        <option value="1">安装（方便测试）</option>
                        <option value="0">不安装</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-install" id="installBtn">开始安装</button>
        </form>
        
        <div class="loading" id="loading">正在安装，请稍候...</div>
        
        <div class="result" id="result"></div>
    </div>
    
    <script>
        const form = document.getElementById('installForm');
        const installBtn = document.getElementById('installBtn');
        const loading = document.getElementById('loading');
        const result = document.getElementById('result');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const config = {
                db_name: document.getElementById('dbName').value.trim(),
                db_host: document.getElementById('dbHost').value.trim(),
                db_port: document.getElementById('dbPort').value.trim(),
                db_user: document.getElementById('dbUser').value.trim(),
                db_password: document.getElementById('dbPassword').value,
                admin_username: document.getElementById('adminUsername').value.trim(),
                admin_password: document.getElementById('adminPassword').value,
                install_sample: document.getElementById('installSample').value === '1'
            };
            
            if (!config.db_name || !config.db_user || !config.db_password || !config.admin_username || !config.admin_password) {
                showResult('error', '请填写所有必填项');
                return;
            }
            
            installBtn.disabled = true;
            loading.style.display = 'block';
            result.className = 'result';
            
            try {
                const response = await fetch('/api/install.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(config),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = '<strong>' + data.message + '</strong><br>';
                    if (data.next) {
                        html += '<br>下一步：<a href="' + data.next + '">' + data.next + '</a>';
                    }
                    showResult('success', html);
                } else {
                    showResult('error', '安装失败：' + (data.message || data.error));
                }
            } catch (error) {
                showResult('error', '请求失败：' + error.message);
            } finally {
                installBtn.disabled = false;
                loading.style.display = 'none';
            }
        });
        
        function showResult(type, content) {
            result.className = 'result ' + type;
            result.innerHTML = content;
        }
    </script>
</body>
</html>
