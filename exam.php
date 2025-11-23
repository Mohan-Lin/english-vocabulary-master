<?php
session_start();
// 检查是否已安装
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit;
}
require_once 'functions.php';
// 检查是否有单词数据
if (empty($_SESSION['exam_words']) || !isset($_SESSION['exam_direction'])) {
    header('Location: index.php');
    exit;
}
$words = $_SESSION['exam_words'];
$direction = $_SESSION['exam_direction'];
$current_index = $_SESSION['current_index'];
$answers = $_SESSION['answers'];
$use_ai = $_SESSION['use_ai'] ?? false;
$show_answer = $_SESSION['show_answer'] ?? 'after_each';
$is_wrong_exam = $_SESSION['is_wrong_exam'] ?? false;
$filters = $_SESSION['filters'] ?? []; // 获取过滤条件
// 检查是否已完成
if ($current_index >= count($words)) {
    header('Location: result.php');
    exit;
}
// 获取当前单词
$current_word = $words[$current_index];
// 确定当前考查方向
$current_direction = $direction;
if ($direction === 'mixed') {
    $current_direction = (rand(0, 1) === 0) ? 'en_to_cn' : 'cn_to_en';
}
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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>单词考查 - 第 <?php echo $current_index + 1; ?> 题</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        var showAnswer = '<?php echo $show_answer; ?>';
        
        // 提交答案的表单处理
        $('#answer-form').on('submit', function(e) {
            e.preventDefault();
            
            // 显示加载动画
            $('#submit-btn').prop('disabled', true).html('<span class="spinner"></span> 分析中...');
            
            // 获取表单数据
            var formData = $(this).serialize();
            
            // 发送AJAX请求
            $.ajax({
                type: 'POST',
                url: 'submit_answer.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // 隐藏提交按钮、不知道按钮，显示下一题按钮
                        $('#submit-btn').hide();
                        $('#dont-know-btn').hide();
                        $('#next-btn').show();
                        
                        // 根据设置决定是否显示答案
                        if (showAnswer === 'after_each') {
                            // 每题后显示答案
                            showAnswerFeedback(response);
                        } else if (showAnswer === 'after_all' && (<?php echo $current_index + 1; ?> >= <?php echo count($words); ?>)) {
                            // 全部完成后显示答案（只有最后一题才显示）
                            showAnswerFeedback(response);
                        }
                        
                        // 如果是错题考查且删除了错题，显示成功提示
                        if (response.is_wrong_exam && response.deleted) {
                            showDeletedSuccess();
                        }
                    } else {
                        alert('提交失败: ' + response.message);
                        $('#submit-btn').prop('disabled', false).html('提交答案');
                    }
                },
                error: function(xhr, status, error) {
                    alert('请求失败: ' + error);
                    $('#submit-btn').prop('disabled', false).html('提交答案');
                }
            });
        });
        
        // 不知道按钮点击事件
        $('#dont-know-btn').on('click', function() {
            // 显示加载动画
            $('#dont-know-btn').prop('disabled', true).html('<span class="spinner"></span> 处理中...');
            
            // 准备数据
            var formData = {
                word_id: <?php echo $current_word['id']; ?>,
                direction: '<?php echo $current_direction; ?>',
                current_index: <?php echo $current_index; ?>,
                answer: 'dont_know'
            };
            
            // 发送AJAX请求
            $.ajax({
                type: 'POST',
                url: 'submit_answer.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // 自动跳转到下一题
                        window.location.href = 'exam.php';
                    } else {
                        alert('处理失败: ' + response.message);
                        $('#dont-know-btn').prop('disabled', false).html('不知道');
                    }
                },
                error: function(xhr, status, error) {
                    alert('请求失败: ' + error);
                    $('#dont-know-btn').prop('disabled', false).html('不知道');
                }
            });
        });
        
        // 下一题按钮点击事件
        $('#next-btn').on('click', function() {
            // 跳转到下一题
            window.location.href = 'exam.php';
        });
        
        // 显示答案反馈
        function showAnswerFeedback(response) {
            var feedbackHtml = `
            <div class="answer-feedback">
                <h3>答题结果</h3>
                <div class="question-review">
                    <div class="result ${response.is_correct ? 'correct' : 'wrong'}">
                        ${response.is_correct ? '✓ 回答正确！' : '✗ 回答错误'}
                    </div>
                    <p><strong>你的答案：</strong> ${response.user_answer}</p>
                    <p><strong>正确答案：</strong> ${response.correct_answer}</p>
                    ${response.ai_feedback ? '<div class="ai-feedback">' + response.ai_feedback + '</div>' : ''}
                </div>
            </div>
            `;
            
            $('#answer-feedback-container').html(feedbackHtml);
        }
        
        // 显示删除成功提示
        function showDeletedSuccess() {
            var successHtml = `
            <div class="alert success deleted-success">
                <strong>恭喜！</strong> 这道题已经从错题列表中移除。
            </div>
            `;
            
            // 添加到页面顶部
            $('body').prepend(successHtml);
            
            // 3秒后自动隐藏
            setTimeout(function() {
                $('.deleted-success').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });
    </script>
    <style>
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .btn-container {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    #next-btn {
        display: none;
    }
    
    #dont-know-btn {
        background-color: #95a5a6;
    }
    
    #dont-know-btn:hover {
        background-color: #7f8c8d;
    }
    
    .exam-header {
        margin-bottom: 20px;
    }
    
    .exam-header p {
        color: #7f8c8d;
        margin-top: 5px;
    }
    
    .deleted-success {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            top: -100px;
            opacity: 0;
        }
        to {
            top: 20px;
            opacity: 1;
        }
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
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
        border-left: 4px solid #007bff;
    }
    
    .word-details .detail-item {
        margin: 8px 0;
        font-size: 14px;
        color: #495057;
    }
    
    .word-details .detail-label {
        font-weight: bold;
        display: inline-block;
        width: 60px;
    }
</style>
</head>
<body>
    <div class="container">
        <?php if ($is_wrong_exam): ?>
            <div class="exam-header">
                <h1>错题专项考查</h1>
                <p>本次考查基于您的错题记录，共 <?php echo count($words); ?> 个单词</p>
                <p style="color: #27ae60; font-weight: bold;">提示：答对的题目将从错题列表中自动删除</p>
            </div>
        <?php else: ?>
            <div class="exam-header">
                <h1>单词考查</h1>
                <?php if (!empty($filter_desc)): ?>
                    <div class="exam-info">
                        <p><strong>当前测试条件：</strong></p>
                        <div class="filter-tags">
                            <?php foreach ($filter_desc as $tag): ?>
                                <span class="filter-tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo ($current_index / count($words)) * 100; ?>%"></div>
            <div class="progress-text">
                第 <?php echo $current_index + 1; ?> 题 / 共 <?php echo count($words); ?> 题
            </div>
        </div>
        
        <div id="answer-feedback-container">
            <!-- 当前题目的答案反馈将动态显示在这里 -->
        </div>
        
        <form id="answer-form" method="POST">
            <input type="hidden" name="word_id" value="<?php echo $current_word['id']; ?>">
            <input type="hidden" name="direction" value="<?php echo $current_direction; ?>">
            <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
            
            <div class="question">
                <?php if ($current_direction === 'en_to_cn'): ?>
                    <div class="word"><?php echo htmlspecialchars($current_word['word']); ?></div>
                    <div class="phonetic"><?php echo htmlspecialchars($current_word['phonetic']); ?></div>
                    <p>请写出这个单词的中文意思：</p>
                <?php else: ?>
                    <div class="meaning"><?php echo htmlspecialchars($current_word['meaning']); ?></div>
                    <p>请写出这个意思对应的英文单词：</p>
                <?php endif; ?>
                
                <!-- 显示单词详细信息 -->
                <div class="word-details">
                    <?php if (!empty($current_word['source'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">来源：</span>
                            <?php echo htmlspecialchars($current_word['source']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($current_word['is_bold']): ?>
                        <div class="detail-item">
                            <span class="detail-label">类型：</span>
                            <span style="color: #dc3545; font-weight: bold;">黑体字单词</span>
                        </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <span class="detail-label">频率：</span>
                        <?php 
                        $frequency_color = [
                            '超高频' => '#dc3545',
                            '高频' => '#ffc107', 
                            '中频' => '#28a745',
                            '低频' => '#6c757d'
                        ];
                        $color = $frequency_color[$current_word['frequency']] ?? '#6c757d';
                        ?>
                        <span style="color: <?php echo $color; ?>; font-weight: bold;">
                            <?php echo htmlspecialchars($current_word['frequency']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="text" name="answer" required autofocus>
                </div>
            </div>
            
            <div class="btn-container">
                <button type="submit" id="submit-btn">提交答案</button>
                <button type="button" id="dont-know-btn" class="btn secondary">不知道</button>
                <button type="button" id="next-btn" class="btn secondary">下一题</button>
            </div>
        </form>
    </div>
</body>
</html>