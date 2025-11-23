<?php
session_start();
// 检查是否已安装
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit;
}
require_once 'functions.php';
// 检查是否完成考试
if (empty($_SESSION['exam_words']) || empty($_SESSION['answers']) || !isset($_SESSION['exam_direction'])) {
    header('Location: index.php');
    exit;
}
$words = $_SESSION['exam_words'];
$answers = $_SESSION['answers'];
$direction = $_SESSION['exam_direction'];
$use_ai = $_SESSION['use_ai'] ?? false;
$is_wrong_exam = $_SESSION['is_wrong_exam'] ?? false;
$filters = $_SESSION['filters'] ?? []; // 获取过滤条件
// 计算得分
$total = count($answers);
$correct = 0;
foreach ($answers as $answer) {
    if ($answer['is_correct']) {
        $correct++;
    }
}
$score = $total > 0 ? round(($correct / $total) * 100, 1) : 0;
// 生成测试条件描述
function get_filter_description($filters) {
    $desc = [];
    
    if (!empty($filters['source']) && $filters['source'] != 'all') {
        $desc[] = "单元：" . $filters['source'];
    }
    
    if (isset($filters['is_bold']) && $filters['is_bold'] != 'all') {
        $desc[] = "黑体字：" . ($filters['is_bold'] == 'yes' ? '是' : '否');
    }
    
    if (!empty($filters['frequency']) && $filters['frequency'] != 'all') {
        $desc[] = "频率：" . $filters['frequency'];
    }
    
    return $desc;
}
$filter_desc = get_filter_description($filters);
// 生成考查方向描述
$direction_text = [
    'en_to_cn' => '英译中',
    'cn_to_en' => '中译英',
    'mixed' => '混合模式'
];
$direction_desc = $direction_text[$direction] ?? '混合模式';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>考查结果 - 高中英语单词考查系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    .result-container {
        text-align: center;
        padding: 30px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }
    
    .score-circle {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        margin: 0 auto 30px;
        position: relative;
        overflow: hidden;
    }
    
    .score-circle::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.1);
        transform: rotate(45deg);
    }
    
    .score-number {
        font-size: 48px;
        font-weight: bold;
        position: relative;
        z-index: 1;
    }
    
    .score-text {
        font-size: 18px;
        position: relative;
        z-index: 1;
    }
    
    .stats {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin: 30px 0;
        flex-wrap: wrap;
    }
    
    .stat-item {
        text-align: center;
        min-width: 120px;
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }
    
    .stat-label {
        font-size: 14px;
        color: #6c757d;
    }
    
    .feedback {
        margin: 20px 0;
        padding: 20px;
        border-radius: 8px;
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
    
    .btn-container {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .btn {
        padding: 12px 24px;
        font-size: 16px;
    }
    
    .review-section {
        margin-top: 40px;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    .review-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #495057;
    }
    
    .question-review {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 8px;
        background-color: #fff;
        border: 1px solid #e9ecef;
    }
    
    .question-review.correct {
        border-left: 4px solid #28a745;
    }
    
    .question-review.wrong {
        border-left: 4px solid #dc3545;
    }
    
    .question-header {
        font-weight: bold;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .question-number {
        font-size: 16px;
        color: #495057;
    }
    
    .question-result {
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: normal;
    }
    
    .question-result.correct {
        background-color: #d4edda;
        color: #155724;
    }
    
    .question-result.wrong {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .question-content {
        margin: 10px 0;
    }
    
    .answer-comparison {
        margin-top: 10px;
        font-size: 14px;
    }
    
    .your-answer {
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .correct-answer {
        color: #28a745;
        font-weight: bold;
    }
    
    .ai-feedback {
        margin-top: 10px;
        padding: 10px;
        background-color: #e9f7fe;
        border-radius: 4px;
        font-size: 14px;
        color: #0069d9;
    }
    
    /* 新添加的样式 */
    .exam-info {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }
    
    .exam-info .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    
    .exam-info .filter-tag {
        background-color: #e9ecef;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 14px;
        color: #495057;
    }
    
    .word-details {
        font-size: 14px;
        color: #6c757d;
        margin-top: 10px;
    }
    
    .word-details .detail-item {
        margin: 3px 0;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="result-container">
            <?php if ($is_wrong_exam): ?>
                <h1>错题专项考查结果</h1>
            <?php else: ?>
                <h1>单词考查结果</h1>
            <?php endif; ?>
            
            <!-- 显示测试条件 -->
            <?php if (!empty($filter_desc) && !$is_wrong_exam): ?>
                <div class="exam-info">
                    <p><strong>测试条件：</strong></p>
                    <div class="filter-tags">
                        <span class="filter-tag">方向：<?php echo $direction_desc; ?></span>
                        <?php foreach ($filter_desc as $tag): ?>
                            <span class="filter-tag"><?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="score-circle">
                <div class="score-number"><?php echo $score; ?></div>
                <div class="score-text">分</div>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $correct; ?></div>
                    <div class="stat-label">答对题数</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total - $correct; ?></div>
                    <div class="stat-label">答错题数</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">总题数</div>
                </div>
            </div>
            
            <div class="feedback">
                <?php if ($score >= 90): ?>
                    <h3>🎉 优秀！</h3>
                    <p>您的词汇掌握情况非常好，继续保持！</p>
                <?php elseif ($score >= 80): ?>
                    <h3>👍 良好！</h3>
                    <p>您的词汇基础扎实，再努力一下就能更上一层楼！</p>
                <?php elseif ($score >= 70): ?>
                    <h3>💪 不错！</h3>
                    <p>您的词汇量还可以，建议加强复习巩固。</p>
                <?php else: ?>
                    <h3>📚 继续努力！</h3>
                    <p>建议您制定系统的词汇学习计划，多练习多复习。</p>
                <?php endif; ?>
                
                <?php if ($is_wrong_exam): ?>
                    <p style="margin-top: 15px; color: #27ae60;">
                        <strong>错题复习提示：</strong> 答对的题目已从错题列表中移除，答错的题目将继续保留在错题列表中供您复习。
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="btn-container">
                <a href="index.php" class="btn primary">重新开始</a>
                <?php if ($total - $correct > 0): ?>
                    <a href="review.php" class="btn secondary">查看错题</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 详细答题回顾 -->
        <div class="review-section">
            <div class="review-title">📋 答题回顾</div>
            
            <?php foreach ($answers as $index => $answer): ?>
                <?php 
                $word = $words[$index];
                $question_type = $answer['direction'] == 'en_to_cn' ? '英译中' : '中译英';
                ?>
                <div class="question-review <?php echo $answer['is_correct'] ? 'correct' : 'wrong'; ?>">
                    <div class="question-header">
                        <span class="question-number">第 <?php echo $index + 1; ?> 题 (<?php echo $question_type; ?>)</span>
                        <span class="question-result <?php echo $answer['is_correct'] ? 'correct' : 'wrong'; ?>">
                            <?php echo $answer['is_correct'] ? '✓ 正确' : '✗ 错误'; ?>
                        </span>
                    </div>
                    
                    <div class="question-content">
                        <?php if ($answer['direction'] == 'en_to_cn'): ?>
                            <strong>单词：</strong><?php echo htmlspecialchars($word['word']); ?> 
                            <?php echo !empty($word['phonetic']) ? '[' . htmlspecialchars($word['phonetic']) . ']' : ''; ?>
                        <?php else: ?>
                            <strong>中文：</strong><?php echo htmlspecialchars($word['meaning']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="answer-comparison">
                        <div class="your-answer">
                            <strong>你的答案：</strong><?php echo htmlspecialchars($answer['user_answer']); ?>
                        </div>
                        <div class="correct-answer">
                            <strong>正确答案：</strong><?php echo htmlspecialchars($answer['correct_answer']); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($answer['ai_feedback'])): ?>
                        <div class="ai-feedback">
                            <strong>AI分析：</strong><?php echo htmlspecialchars($answer['ai_feedback']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- 显示单词详细信息 -->
                    <div class="word-details">
                        <?php if (!empty($word['source'])): ?>
                            <div class="detail-item">
                                <strong>来源：</strong><?php echo htmlspecialchars($word['source']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($word['is_bold']): ?>
                            <div class="detail-item">
                                <strong>类型：</strong><span style="color: #dc3545;">黑体字单词</span>
                            </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <strong>频率：</strong>
                            <?php 
                            $frequency_color = [
                                '超高频' => '#dc3545',
                                '高频' => '#ffc107', 
                                '中频' => '#28a745',
                                '低频' => '#6c757d'
                            ];
                            $color = $frequency_color[$word['frequency']] ?? '#6c757d';
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars($word['frequency']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
    // 页面加载完成后的动画效果
    document.addEventListener('DOMContentLoaded', function() {
        // 添加分数动画
        const scoreNumber = document.querySelector('.score-number');
        const finalScore = parseInt(scoreNumber.textContent);
        let currentScore = 0;
        
        const scoreAnimation = setInterval(function() {
            currentScore += Math.ceil(finalScore / 50);
            if (currentScore >= finalScore) {
                currentScore = finalScore;
                clearInterval(scoreAnimation);
            }
            scoreNumber.textContent = currentScore;
        }, 30);
    });
    </script>
</body>
</html>