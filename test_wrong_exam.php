<?php
// test_fix.php - 测试修复效果
require_once 'functions.php';

echo "<h1>错题考查功能修复测试</h1>";

try {
    // 连接数据库
    $conn = db_connect();
    echo "<p style='color: green;'>✓ 数据库连接成功</p>";
    
    // 获取当前用户IP
    $user_ip = $_SERVER['REMOTE_ADDR'];
    echo "<p>当前用户IP: " . htmlspecialchars($user_ip) . "</p>";
    
    // 测试get_wrong_words_for_exam函数
    echo "<h2>测试get_wrong_words_for_exam()函数</h2>";
    $wrong_words_exam = get_wrong_words_for_exam();
    echo "<p>返回的错题数量: " . count($wrong_words_exam) . "</p>";
    
    if (!empty($wrong_words_exam)) {
        echo "<div style='color: green;'>✓ 成功获取到错题数据！</div>";
        echo "<h3>错题列表:</h3>";
        echo "<ul>";
        foreach ($wrong_words_exam as $word) {
            echo "<li><strong>" . htmlspecialchars($word['word']) . "</strong> - " . htmlspecialchars($word['meaning']) . "</li>";
        }
        echo "</ul>";
        
        echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>修复成功！</strong> 现在您可以正常开始错题考查了。";
        echo "</div>";
    } else {
        echo "<div style='color: orange;'>⚠️ 仍然没有获取到错题数据</div>";
        
        // 检查wrong_words表是否有数据
        echo "<h3>检查wrong_words表数据:</h3>";
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wrong_words WHERE user_ip = ?");
        $stmt->bind_param("s", $user_ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];
        $stmt->close();
        
        echo "<p>wrong_words表中当前IP的记录数: " . $count . "</p>";
        
        if ($count > 0) {
            echo "<p>发现问题: wrong_words表有数据，但JOIN查询失败</p>";
            
            // 检查words表是否有对应记录
            $stmt = $conn->prepare("
                SELECT ww.word_id, w.id as word_exists 
                FROM wrong_words ww
                LEFT JOIN words w ON ww.word_id = w.id
                WHERE ww.user_ip = ?
                LIMIT 10
            ");
            $stmt->bind_param("s", $user_ip);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo "<h4>数据完整性检查:</h4>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>word_id</th><th>word_exists</th><th>状态</th></tr>";
            
            $missing_words = 0;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['word_id']) . "</td>";
                echo "<td>" . ($row['word_exists'] ? htmlspecialchars($row['word_exists']) : "NULL") . "</td>";
                
                if ($row['word_exists']) {
                    echo "<td style='color: green;'>✓ 正常</td>";
                } else {
                    echo "<td style='color: red;'>✗ 缺失</td>";
                    $missing_words++;
                }
                
                echo "</tr>";
            }
            echo "</table>";
            
            if ($missing_words > 0) {
                echo "<p style='color: red;'>发现 {$missing_words} 个word_id在words表中没有对应记录！</p>";
                echo "<p>这是数据完整性问题，需要修复。</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 测试失败: " . $e->getMessage() . "</p>";
}
?>