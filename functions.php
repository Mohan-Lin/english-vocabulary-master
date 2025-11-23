<?php
// 数据库连接
function db_connect() {
    static $conn;
    
    if (!isset($conn)) {
        require_once 'config.php';
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("数据库连接失败: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// 创建数据库表
function create_tables() {
    $conn = db_connect();
    
    // 创建单词表
    $sql = "CREATE TABLE IF NOT EXISTS `words` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `word` varchar(100) NOT NULL,
        `phonetic` varchar(100) DEFAULT NULL,
        `meaning` text NOT NULL,
        `is_bold` BOOLEAN DEFAULT FALSE COMMENT '是否黑体字',
        `source` varchar(255) DEFAULT NULL COMMENT '来源（如英语必修一 Welcome Unit）',
        `frequency` ENUM('超高频', '高频', '中频', '低频') DEFAULT '中频' COMMENT '考查频率',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `word` (`word`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        throw new Exception("创建单词表失败: " . $conn->error);
    }
    
    // 创建错题记录表
    $sql = "CREATE TABLE IF NOT EXISTS `wrong_words` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `word_id` int(11) NOT NULL,
        `user_ip` varchar(45) DEFAULT NULL,
        `wrong_answer` varchar(255) DEFAULT NULL,
        `direction` enum('en_to_cn','cn_to_en','mixed') DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `word_id` (`word_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        throw new Exception("创建错题记录表失败: " . $conn->error);
    }
    
    // 创建AI缓存表
    $sql = "CREATE TABLE IF NOT EXISTS `ai_cache` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cache_key` varchar(32) NOT NULL,
        `word_id` int(11) NOT NULL,
        `user_answer` varchar(255) NOT NULL,
        `direction` enum('en_to_cn','cn_to_en') NOT NULL,
        `ai_result` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `cache_key` (`cache_key`),
        KEY `word_id` (`word_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        throw new Exception("创建AI缓存表失败: " . $conn->error);
    }
}

// 初始化单词数据
function init_words() {
    $conn = db_connect();
    
    // 检查是否已有数据
    $result = $conn->query("SELECT COUNT(*) as count FROM words");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return; // 已有数据，不再初始化
    }
    
    // 高考英语词汇示例
    $words = [
        ['word' => 'abandon', 'phonetic' => '[əˈbændən]', 'meaning' => '抛弃，放弃', 'is_bold' => 0, 'source' => '英语必修一', 'frequency' => '中频'],
        ['word' => 'ability', 'phonetic' => '[əˈbɪləti]', 'meaning' => '能力，才能', 'is_bold' => 1, 'source' => '英语必修一', 'frequency' => '高频'],
        ['word' => 'abroad', 'phonetic' => '[əˈbrɔːd]', 'meaning' => '在国外，海外', 'is_bold' => 0, 'source' => '英语必修一', 'frequency' => '中频'],
        ['word' => 'absent', 'phonetic' => '[ˈæbsənt]', 'meaning' => '缺席的，不在的', 'is_bold' => 0, 'source' => '英语必修一', 'frequency' => '低频'],
        ['word' => 'absolute', 'phonetic' => '[ˈæbsəluːt]', 'meaning' => '绝对的，完全的', 'is_bold' => 1, 'source' => '英语必修一', 'frequency' => '中频'],
        ['word' => 'absorb', 'phonetic' => '[əbˈsɔːrb]', 'meaning' => '吸收，吸引', 'is_bold' => 0, 'source' => '英语必修一', 'frequency' => '中频'],
        ['word' => 'academic', 'phonetic' => '[ˌækəˈdemɪk]', 'meaning' => '学术的，教学的', 'is_bold' => 0, 'source' => '英语必修一', 'frequency' => '低频'],
        ['word' => 'accent', 'phonetic' => '[ˈæksənt]', 'meaning' => '口音，重音', 'is_bold' => 1, 'source' => '英语必修一', 'frequency' => '中频'],
        ['word' => 'accept', 'phonetic' => '[əkˈsept]', 'meaning' => '接受，同意', 'is_bold' => 1, 'source' => '英语必修一', 'frequency' => '高频'],
        ['word' => 'accident', 'phonetic' => '[ˈæksɪdənt]', 'meaning' => '事故，意外', 'is_bold' => 1, 'source' => '英语必修一', 'frequency' => '高频'],
        ['word' => 'accommodate', 'phonetic' => '[əˈkɒmədeɪt]', 'meaning' => '容纳，提供住宿', 'is_bold' => 0, 'source' => '英语必修二', 'frequency' => '低频'],
        ['word' => 'accomplish', 'phonetic' => '[əˈkʌmplɪʃ]', 'meaning' => '完成，实现', 'is_bold' => 0, 'source' => '英语必修二', 'frequency' => '中频'],
        ['word' => 'account', 'phonetic' => '[əˈkaʊnt]', 'meaning' => '账户，描述', 'is_bold' => 1, 'source' => '英语必修二', 'frequency' => '高频'],
        ['word' => 'accumulate', 'phonetic' => '[əˈkjuːmjəleɪt]', 'meaning' => '积累，积聚', 'is_bold' => 0, 'source' => '英语必修二', 'frequency' => '中频'],
        ['word' => 'accurate', 'phonetic' => '[ˈækjərət]', 'meaning' => '准确的，精确的', 'is_bold' => 1, 'source' => '英语必修二', 'frequency' => '中频'],
        ['word' => 'accuse', 'phonetic' => '[əˈkjuːz]', 'meaning' => '指责，控告', 'is_bold' => 0, 'source' => '英语必修二', 'frequency' => '低频'],
        ['word' => 'achieve', 'phonetic' => '[əˈtʃiːv]', 'meaning' => '达到，获得', 'is_bold' => 1, 'source' => '英语必修二', 'frequency' => '高频'],
        ['word' => 'acknowledge', 'phonetic' => '[əkˈnɒlɪdʒ]', 'meaning' => '承认，答谢', 'is_bold' => 0, 'source' => '英语必修二', 'frequency' => '中频'],
        ['word' => 'acquire', 'phonetic' => '[əˈkwaɪə(r)]', 'meaning' => '获得，取得', 'is_bold' => 1, 'source' => '英语必修二', 'frequency' => '高频'],
        ['word' => 'adapt', 'phonetic' => '[əˈdæpt]', 'meaning' => '适应，改编', 'is_bold' => 1, 'source' => '英语必修二', 'frequency' => '高频']
    ];
    
    // 插入单词数据
    foreach ($words as $word) {
        $stmt = $conn->prepare("INSERT INTO words (word, phonetic, meaning, is_bold, source, frequency) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $word['word'], $word['phonetic'], $word['meaning'], $word['is_bold'], $word['source'], $word['frequency']);
        $stmt->execute();
        $stmt->close();
    }
}

// 获取随机单词
function get_random_words($count) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT * FROM words ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("i", $count);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $words = [];
    while ($row = $result->fetch_assoc()) {
        $words[] = $row;
    }
    
    $stmt->close();
    return $words;
}

// 获取所有来源选项
function get_all_sources() {
    $conn = db_connect();
    $sql = "SELECT DISTINCT source FROM words WHERE source IS NOT NULL ORDER BY source";
    $result = $conn->query($sql);
    
    $sources = [];
    while ($row = $result->fetch_assoc()) {
        $sources[] = $row['source'];
    }
    
    return $sources;
}

// 根据过滤条件获取单词
function get_filtered_words($count, $filters) {
    $conn = db_connect();
    
    // 构建查询条件
    $conditions = [];
    $params = [];
    $param_types = "";
    
    // 来源过滤
    if (!empty($filters['source']) && $filters['source'] != 'all') {
        $conditions[] = "source = ?";
        $params[] = $filters['source'];
        $param_types .= "s";
    }
    
    // 黑体字过滤
    if (isset($filters['is_bold']) && $filters['is_bold'] != 'all') {
        $is_bold = ($filters['is_bold'] == 'yes') ? 1 : 0;
        $conditions[] = "is_bold = ?";
        $params[] = $is_bold;
        $param_types .= "i";
    }
    
    // 频率过滤
    if (!empty($filters['frequency']) && $filters['frequency'] != 'all') {
        $conditions[] = "frequency = ?";
        $params[] = $filters['frequency'];
        $param_types .= "s";
    }
    
    // 构建SQL查询
    $sql = "SELECT * FROM words";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY RAND() LIMIT ?";
    
    // 添加数量参数
    $params[] = $count;
    $param_types .= "i";
    
    // 执行查询
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $words = [];
    while ($row = $result->fetch_assoc()) {
        $words[] = $row;
    }
    
    $stmt->close();
    return $words;
}

// 优化后的AI分析函数
function ai_check_answer($word, $user_answer, $direction) {
    require_once 'config.php';
    
    // 如果没有配置AI功能，直接返回false
    if (!defined('ALIYUN_API_KEY') || empty(ALIYUN_API_KEY)) {
        return false;
    }
    
    $apiKey = ALIYUN_API_KEY;
    $url = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
    
    // 简化系统提示，减少不必要的内容
    $system_prompt = "作为英语词汇判断AI，请评估用户答案的正确性，返回以下三种结果之一：
1. 完全正确：返回 'true'
2. 完全错误：返回 'wrong'
3. 意思相近但不准确：返回 'inaccurate|更准确的翻译'
不要添加任何其他内容。";
    
    // 根据考查方向构建问题
    if ($direction === 'en_to_cn') {
        $question = "单词: {$word['word']}\n用户答案: {$user_answer}";
    } else {
        $question = "中文: {$word['meaning']}\n用户答案: {$user_answer}";
    }
    
    $data = [
        "model" => "qwen-turbo", // 使用更轻量的模型
        "messages" => [
            [
                "role" => "system",
                "content" => $system_prompt
            ],
            [
                "role" => "user",
                "content" => $question
            ]
        ],
        "max_tokens" => 100 // 限制返回长度
    ];
    
    $headers = [
        'Authorization: Bearer '.$apiKey,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5秒超时
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 连接超时2秒
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_msg = 'AI分析错误: ' . curl_error($ch);
        error_log($error_msg);
        curl_close($ch);
        return false;
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        error_log("AI分析API返回非200状态码: {$http_code}, 响应: {$response}");
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("AI分析API返回的JSON解析失败: " . json_last_error_msg() . " 原始响应: {$response}");
        return false;
    }
    
    $ai_result = $response_data['choices'][0]['message']['content'] ?? '';
    
    return $ai_result;
}

// 获取缓存的AI结果
function get_cached_ai_result($word_id, $user_answer, $direction) {
    $conn = db_connect();
    $cache_key = md5("{$word_id}_{$user_answer}_{$direction}");
    
    $stmt = $conn->prepare("SELECT ai_result FROM ai_cache WHERE cache_key = ? AND created_at > NOW() - INTERVAL 7 DAY");
    $stmt->bind_param("s", $cache_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['ai_result'];
    }
    
    return false;
}

// 保存AI结果到缓存
function save_ai_cache($word_id, $user_answer, $direction, $ai_result) {
    $conn = db_connect();
    $cache_key = md5("{$word_id}_{$user_answer}_{$direction}");
    
    $stmt = $conn->prepare("REPLACE INTO ai_cache (cache_key, word_id, user_answer, direction, ai_result) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $cache_key, $word_id, $user_answer, $direction, $ai_result);
    $stmt->execute();
    $stmt->close();
}

// 修改：检查答案函数，添加缓存支持和错误处理
function check_answer($correct, $user_answer, $direction, $use_ai = false, $word = null) {
    // 转换为小写并去除多余空格
    $user_answer = strtolower(trim($user_answer));
    $correct = strtolower(trim($correct));
    
    $basic_result = false;
    
    if ($direction == 'cn_to_en') {
        // 对于中译英，直接比较单词
        $basic_result = ($user_answer === $correct);
    } else {
        // 对于英译中，处理多义词和意思相近的情况
        $correct_meanings = explode('，', $correct); // 使用中文逗号分隔多义项
        $user_meanings = explode('，', $user_answer);
        
        foreach ($user_meanings as $user_meaning) {
            $user_meaning = trim($user_meaning);
            foreach ($correct_meanings as $correct_meaning) {
                $correct_meaning = trim($correct_meaning);
                // 完全匹配或部分匹配（意思相近）
                if ($user_meaning === $correct_meaning || 
                    similarity($user_meaning, $correct_meaning) > 0.7) {
                    $basic_result = true;
                    break 2;
                }
            }
        }
    }
    
    $ai_feedback = null;
    
    // 如果启用了AI分析
    if ($use_ai && $word) {
        try {
            // 先尝试从缓存获取
            $cached_result = get_cached_ai_result($word['id'], $user_answer, $direction);
            
            if ($cached_result !== false) {
                $ai_result = $cached_result;
            } else {
                // 没有缓存则调用API
                $ai_result = ai_check_answer($word, $user_answer, $direction);
                
                // 保存到缓存
                if ($ai_result !== false) {
                    save_ai_cache($word['id'], $user_answer, $direction, $ai_result);
                }
            }
            
            // 解析AI结果
            if ($ai_result === 'true') {
                $ai_feedback = 'AI分析: 答案正确';
                return ['result' => true, 'ai_feedback' => $ai_feedback];
            } elseif ($ai_result === 'wrong') {
                $ai_feedback = 'AI分析: 答案错误';
                return ['result' => false, 'ai_feedback' => $ai_feedback];
            } elseif (strpos($ai_result, 'inaccurate') === 0) {
                $parts = explode('|', $ai_result);
                $suggestion = count($parts) > 1 ? $parts[1] : '建议参考标准答案';
                $ai_feedback = 'AI分析: 答案不准确。'.$suggestion;
                return ['result' => false, 'ai_feedback' => $ai_feedback];
            } else {
                // AI分析失败，使用基本结果
                $ai_feedback = 'AI分析: 无法确定答案';
                return ['result' => $basic_result, 'ai_feedback' => $ai_feedback];
            }
        } catch (Exception $e) {
            error_log("AI分析过程中出错: " . $e->getMessage());
            $ai_feedback = 'AI分析: 服务暂时不可用';
            return ['result' => $basic_result, 'ai_feedback' => $ai_feedback];
        }
    }
    
    return ['result' => $basic_result, 'ai_feedback' => $ai_feedback];
}

// 简单的相似度计算函数
function similarity($str1, $str2) {
    similar_text($str1, $str2, $percent);
    return $percent / 100;
}

// 记录错题
function record_wrong_word($word_id, $user_ip, $wrong_answer, $direction) {
    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO wrong_words (word_id, user_ip, wrong_answer, direction) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $word_id, $user_ip, $wrong_answer, $direction);
    $stmt->execute();
    $stmt->close();
}

// 获取错题记录
function get_wrong_words($limit = 50) {
    $conn = db_connect();
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("
        SELECT w.id, w.word, w.phonetic, w.meaning, w.is_bold, w.source, w.frequency, 
               ww.wrong_answer, ww.direction, ww.created_at 
        FROM wrong_words ww
        JOIN words w ON ww.word_id = w.id
        WHERE ww.user_ip = ?
        ORDER BY ww.created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("si", $user_ip, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $wrong_words = [];
    while ($row = $result->fetch_assoc()) {
        $wrong_words[] = $row;
    }
    
    $stmt->close();
    return $wrong_words;
}

// 获取错题单词用于考查 - 支持数量参数
function get_wrong_words_for_exam($limit = 10) {
    $conn = db_connect();
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // 添加错误处理
    if (!$conn) {
        error_log("数据库连接失败");
        return [];
    }
    
    // 验证数量参数
    if ($limit < 1) {
        $limit = 1;
    } elseif ($limit > 100) {
        $limit = 100;
    }
    
    // 使用LEFT JOIN替代INNER JOIN，移除DISTINCT
    $stmt = $conn->prepare("
        SELECT w.id, w.word, w.phonetic, w.meaning, w.is_bold, w.source, w.frequency
        FROM wrong_words ww
        LEFT JOIN words w ON ww.word_id = w.id
        WHERE ww.user_ip = ?
        AND w.id IS NOT NULL  -- 确保只返回有对应单词的记录
        ORDER BY ww.created_at DESC
        LIMIT ?
    ");
    
    // 检查prepare是否成功
    if (!$stmt) {
        error_log("SQL prepare失败: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("si", $user_ip, $limit);
    $stmt->execute();
    
    // 检查执行是否成功
    if ($stmt->errno) {
        error_log("SQL执行失败: " . $stmt->error);
        $stmt->close();
        return [];
    }
    
    $result = $stmt->get_result();
    
    $wrong_words = [];
    while ($row = $result->fetch_assoc()) {
        // 确保返回的数据格式正确
        $wrong_words[] = [
            'id' => $row['id'],
            'word' => $row['word'],
            'phonetic' => $row['phonetic'],
            'meaning' => $row['meaning'],
            'is_bold' => $row['is_bold'],
            'source' => $row['source'],
            'frequency' => $row['frequency']
        ];
    }
    
    $stmt->close();
    
    // 调试：记录返回的错题数量
    error_log("get_wrong_words_for_exam返回数量: " . count($wrong_words));
    
    return $wrong_words;
}

// 清空错题库
function clear_wrong_words() {
    $conn = db_connect();
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // 添加错误处理
    if (!$conn) {
        error_log("数据库连接失败");
        return 0;
    }
    
    $stmt = $conn->prepare("DELETE FROM wrong_words WHERE user_ip = ?");
    
    // 检查prepare是否成功
    if (!$stmt) {
        error_log("SQL prepare失败: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("s", $user_ip);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    return $affected_rows;
}

// 新增：删除特定单词的错题记录
function delete_wrong_word_by_word_id($word_id) {
    $conn = db_connect();
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // 添加错误处理
    if (!$conn) {
        error_log("数据库连接失败");
        return 0;
    }
    
    $stmt = $conn->prepare("DELETE FROM wrong_words WHERE user_ip = ? AND word_id = ?");
    
    // 检查prepare是否成功
    if (!$stmt) {
        error_log("SQL prepare失败: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("si", $user_ip, $word_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    error_log("删除错题记录: word_id={$word_id}, 影响行数: {$affected_rows}");
    
    return $affected_rows;
}

// 获取用户IP
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // 如果有多个IP地址，取第一个
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
?>