<?php
// 检查是否已安装
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>更新日志 - 高中英语单词考查系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    .changelog-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .version {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #4caf50;
    }
    
    .version-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 10px;
    }
    
    .version-number {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .version-date {
        color: #6c757d;
        font-style: italic;
    }
    
    .changelog-section {
        margin-bottom: 15px;
    }
    
    .changelog-section h3 {
        color: #4caf50;
        margin-bottom: 10px;
        font-size: 18px;
    }
    
    .changelog-list {
        list-style: none;
        padding: 0;
    }
    
    .changelog-list li {
        margin-bottom: 8px;
        padding-left: 20px;
        position: relative;
    }
    
    .changelog-list li:before {
        content: "•";
        position: absolute;
        left: 0;
        color: #4caf50;
        font-weight: bold;
    }
    
    .new-feature {
        color: #27ae60;
    }
    
    .improvement {
        color: #3498db;
    }
    
    .bug-fix {
        color: #e74c3c;
    }
    
    .version-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    .nav-link {
        color: #4caf50;
        text-decoration: none;
        font-weight: bold;
    }
    
    .nav-link:hover {
        text-decoration: underline;
    }
    
    .current-version {
        background-color: #e8f5e8;
        border-left-color: #27ae60;
    }
    
    .back-to-home {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #4caf50;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }
    
    .back-to-home:hover {
        background-color: #45a049;
    }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>高中英语单词考查系统 - 更新日志</h1>
        </header>
        
        <div class="changelog-container">
            <!-- v1.3.2 -->
            <div class="version current-version">
                <div class="version-header">
                    <div class="version-number">v1.3.2</div>
                    <div class="version-date">2025年11月19日</div>
                </div>
                
                <div class="changelog-section">
                    <h3>📱 移动端显示优化</h3>
                    <ul class="changelog-list">
                        <li class="bug-fix">修复result.php表格在手机端溢出页面显示的问题</li>
                        <li class="bug-fix">修复review.php表格在手机端溢出页面显示的问题</li>
                        <li class="bug-fix">修复index.php折叠内容在手机端显示不全的问题</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>🔧 响应式设计增强</h3>
                    <ul class="changelog-list">
                        <li class="improvement">添加响应式表格容器，支持水平滚动</li>
                        <li class="improvement">在小屏幕设备上智能隐藏次要列（音标、记录时间）</li>
                        <li class="improvement">优化折叠内容的展开动画和显示效果</li>
                        <li class="improvement">增强移动端按钮布局和点击体验</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>🎨 界面细节优化</h3>
                    <ul class="changelog-list">
                        <li class="improvement">优化表格单元格文字换行处理</li>
                        <li class="improvement">调整移动端内边距和间距设置</li>
                        <li class="improvement">增强表格表头的粘性定位效果</li>
                    </ul>
                </div>
            </div>
            
            <!-- v1.3.1 -->
            <div class="version">
                <div class="version-header">
                    <div class="version-number">v1.3.1</div>
                    <div class="version-date">2025年11月19日</div>
                </div>
                
                <div class="changelog-section">
                    <h3>✨ 界面优化</h3>
                    <ul class="changelog-list">
                        <li class="improvement">简化首页布局，去除冗余内容，提升用户体验</li>
                        <li class="improvement">优化导航结构，使功能入口更加清晰</li>
                        <li class="improvement">添加更新日志页面，方便用户了解版本更新内容</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>🔧 性能优化</h3>
                    <ul class="changelog-list">
                        <li class="improvement">减少首页加载时间，提升系统响应速度</li>
                        <li class="improvement">优化移动端适配，确保在各种设备上的良好显示</li>
                    </ul>
                </div>
            </div>
            
            <!-- v1.3 -->
            <div class="version">
                <div class="version-header">
                    <div class="version-number">v1.3</div>
                    <div class="version-date">2025年11月18日</div>
                </div>
                
                <div class="changelog-section">
                    <h3>🐛 问题修复</h3>
                    <ul class="changelog-list">
                        <li class="bug-fix">修复显示正确答案选项不生效的问题</li>
                        <li class="bug-fix">删除首页重复的按钮</li>
                        <li class="bug-fix">答完题目后自动隐藏"不知道"按钮，避免误点击</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>🔧 功能优化</h3>
                    <ul class="changelog-list">
                        <li class="improvement">优化答题反馈机制，提供更及时的用户提示</li>
                        <li class="improvement">增强错误处理和日志记录功能</li>
                    </ul>
                </div>
            </div>
            
            <!-- v1.2 -->
            <div class="version">
                <div class="version-header">
                    <div class="version-number">v1.2</div>
                    <div class="version-date">2025年11月17日</div>
                </div>
                
                <div class="changelog-section">
                    <h3>✨ 新功能</h3>
                    <ul class="changelog-list">
                        <li class="new-feature">添加错题考查数量选择功能（1-100题）</li>
                        <li class="new-feature">实现答对后自动删除错题功能</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>📱 用户体验</h3>
                    <ul class="changelog-list">
                        <li class="improvement">添加删除成功提示信息</li>
                        <li class="improvement">优化错题考查界面设计</li>
                        <li class="improvement">增强进度显示和状态提示</li>
                    </ul>
                </div>
            </div>
            
            <!-- v1.1 -->
            <div class="version">
                <div class="version-header">
                    <div class="version-number">v1.1</div>
                    <div class="version-date">2025年11月16日</div>
                </div>
                
                <div class="changelog-section">
                    <h3>🧠 AI功能增强</h3>
                    <ul class="changelog-list">
                        <li class="new-feature">添加AI分析答案缓存机制，提升响应速度</li>
                        <li class="improvement">优化AI提示词，提高分析准确性</li>
                        <li class="improvement">添加错误处理和超时控制</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>🔧 系统优化</h3>
                    <ul class="changelog-list">
                        <li class="improvement">增强数据库连接稳定性</li>
                        <li class="improvement">优化单词选择算法</li>
                    </ul>
                </div>
            </div>
            
            <!-- v1.0 -->
            <div class="version">
                <div class="version-header">
                    <div class="version-number">v1.0</div>
                    <div class="version-date">2025年11月15日</div>
                </div>
                
                <div class="changelog-section">
                    <h3>🚀 系统发布</h3>
                    <ul class="changelog-list">
                        <li class="new-feature">基础单词考查功能（英译中、中译英、混合模式）</li>
                        <li class="new-feature">错题记录和复习功能</li>
                        <li class="new-feature">AI分析答案功能</li>
                        <li class="new-feature">学习进度追踪</li>
                        <li class="new-feature">响应式设计，支持移动端</li>
                    </ul>
                </div>
                
                <div class="changelog-section">
                    <h3>📚 核心功能</h3>
                    <ul class="changelog-list">
                        <li class="new-feature">高考3500+核心词汇库</li>
                        <li class="new-feature">自定义考查设置</li>
                        <li class="new-feature">详细的答题反馈</li>
                        <li class="new-feature">错题智能管理</li>
                    </ul>
                </div>
            </div>
            
            <div class="version-navigation">
                <a href="#" class="nav-link disabled">上一页</a>
                <span class="current-page">显示全部版本</span>
                <a href="#" class="nav-link disabled">下一页</a>
            </div>
            
            <a href="index.php" class="back-to-home">返回首页</a>
        </div>
    </div>
</body>
</html>