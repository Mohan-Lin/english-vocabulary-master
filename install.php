<?php
// 错误报告开启
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查是否已安装
if (file_exists('config.php')) {
    header('Location: index.php');
    exit;
}

// 处理安装表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $aliyun_api_key = $_POST['aliyun_api_key'] ?? '';
    
    // 尝试连接数据库
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass);
        
        if ($conn->connect_error) {
            throw new Exception("数据库连接失败: " . $conn->connect_error);
        }
        
        // 创建配置文件
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '$db_host');\n";
        $config_content .= "define('DB_USER', '$db_user');\n";
        $config_content .= "define('DB_PASS', '$db_pass');\n";
        $config_content .= "define('DB_NAME', '$db_user'); // 虚拟主机特性：数据库名=用户名\n";
        $config_content .= "define('ALIYUN_API_KEY', '$aliyun_api_key'); // 阿里云API密钥\n";
        
        if (file_put_contents('config.php', $config_content) === false) {
            throw new Exception("无法创建配置文件，请检查目录权限");
        }
        
        // 包含配置文件
        require_once 'config.php';
        
        // 创建数据库表
        require_once 'functions.php';
        create_tables();
        
        // 初始化单词数据
        init_words();
        
        // 重定向到主页
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>单词考查系统 - 安装向导</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>单词考查系统安装向导</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="db_host">数据库主机：</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label for="db_user">数据库用户名：</label>
                <input type="text" id="db_user" name="db_user" required>
                <small>虚拟主机特性：数据库名=数据库用户名</small>
            </div>
            
            <div class="form-group">
                <label for="db_pass">数据库密码：</label>
                <input type="password" id="db_pass" name="db_pass" required>
            </div>
            
            <div class="form-group">
                <label for="aliyun_api_key">阿里云API密钥（可选）：</label>
                <input type="password" id="aliyun_api_key" name="aliyun_api_key">
                <small>用于AI分析功能，如不需要可留空</small>
            </div>
            
            <div class="form-group">
                <button type="submit">开始安装</button>
            </div>
        </form>
    </div>
</body>
</html>