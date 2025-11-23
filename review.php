<?php
// 检查是否已安装
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit;
}
require_once 'functions.php';

// 处理导出请求
if (isset($_GET['action']) && $_GET['action'] === 'export_wrong_words') {
    try {
        $wrong_words = get_wrong_words(1000); // 获取最多1000条错题
        
        if (empty($wrong_words)) {
            header('Location: review.php?error=暂无错题记录可导出');
            exit;
        }
        
        // 设置CSV文件头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="错题记录_' . date('YmdHis') . '.csv"');
        
        // 创建输出流
        $output = fopen('php://output', 'w');
        
        // 添加BOM头，解决中文乱码问题
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // 写入CSV标题行
        fputcsv($output, [
            '序号',
            '单词',
            '音标',
            '正确意思',
            '您的错误答案',
            '考查方向',
            '记录时间',
            '是否黑体',
            '来源',
            '频率'
        ]);
        
        // 写入错题数据
        foreach ($wrong_words as $index => $word) {
            // 处理换行符和特殊字符
            $clean_meaning = str_replace(["\r\n", "\r", "\n"], " ", $word['meaning']);
            $clean_wrong_answer = str_replace(["\r\n", "\r", "\n"], " ", $word['wrong_answer']);
            
            fputcsv($output, [
                $index + 1, // 序号
                $word['word'],
                $word['phonetic'] ?: '',
                $clean_meaning,
                $clean_wrong_answer,
                $word['direction'] === 'en_to_cn' ? '英译中' : '中译英',
                $word['created_at'],
                $word['is_bold'] ? '是' : '否',
                $word['source'] ?: '',
                $word['frequency'] ?: ''
            ]);
        }
        
        // 写入说明信息
        fputcsv($output, []); // 空行
        fputcsv($output, ['使用说明：']);
        fputcsv($output, ['1. 用Excel或WPS打开此文件后，建议调整列宽以完整显示内容']);
        fputcsv($output, ['2. 可设置行高为"自动调整"以显示完整内容']);
        fputcsv($output, ['3. 建议将"正确意思"和"您的错误答案"列设置为自动换行']);
        fputcsv($output, ['4. 如需打印，建议设置页面为横向打印']);
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        header('Location: review.php?error=导出失败: ' . urlencode($e->getMessage()));
        exit;
    }
}

// 处理清空错题请求
if (isset($_POST['action']) && $_POST['action'] === 'clear_wrong_words') {
    try {
        $affected_rows = clear_wrong_words();
        $success = "成功清空了 {$affected_rows} 条错题记录";
    } catch (Exception $e) {
        $error = "清空错题失败: " . $e->getMessage();
    }
}

// 获取错题记录
$wrong_words = get_wrong_words(100); // 获取最多100条错题
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>错题复习 - 高中英语单词考查系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    .wrong-words-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    
    .wrong-words-table th, .wrong-words-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .wrong-words-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    .wrong-words-table tr:hover {
        background-color: #f5f5f5;
    }
    
    .word-cell {
        font-weight: bold;
        white-space: nowrap;
    }
    
    .meaning-cell {
        max-width: 300px;
    }
    
    .wrong-answer-cell {
        color: #e74c3c;
        font-style: italic;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    /* 修复：统一按钮宽度 */
    .action-buttons .btn {
        flex: 1;
        min-width: 120px;
        text-align: center;
        padding: 10px 15px;
        font-size: 14px;
    }
    
    /* 修复：导出按钮特殊样式 */
    .btn.export {
        background: #27ae60;
    }
    
    .btn.export:hover {
        background: #219653;
    }
    
    .exam-tip {
        background-color: #e8f5e8;
        border-left: 4px solid #27ae60;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    
    .exam-tip strong {
        color: #27ae60;
    }
    
    /* 单词属性标签样式 */
    .word-tag {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .tag-bold {
        background-color: #ffe0b2;
        color: #e65100;
    }
    
    .tag-source {
        background-color: #e1f5fe;
        color: #0277bd;
    }
    
    .tag-frequency {
        background-color: #f3e5f5;
        color: #6a1b9a;
    }
    
    .frequency-超高频 { background-color: #ffebee; color: #c62828; }
    .frequency-高频 { background-color: #fff3e0; color: #ef6c00; }
    .frequency-中频 { background-color: #e8f5e9; color: #2e7d32; }
    .frequency-低频 { background-color: #f5f5f5; color: #616161; }
    
    /* 导出说明样式 */
    .export-guide {
        background-color: #fff3e0;
        border-left: 4px solid #ff9800;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    
    .export-guide h4 {
        margin-top: 0;
        color: #e65100;
    }
    
    .export-guide ol {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .export-guide li {
        margin-bottom: 8px;
    }
    
    /* 响应式优化 */
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .action-buttons .btn {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .word-cell {
            white-space: normal;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <h1>错题复习</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error) || isset($_GET['error'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($error ?? $_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="exam-tip">
            <strong>错题考查功能已更新！</strong><br>
            ✓ 可以选择考查的题目数量<br>
            ✓ 答对的题目将从错题列表中自动删除<br>
            ✓ 帮助您高效复习，巩固记忆
        </div>
        
        <div class="export-guide">
            <h4>📋 CSV导出使用说明</h4>
            <ol>
                <li>点击"导出CSV"按钮下载错题文件</li>
                <li>用Excel或WPS打开下载的CSV文件</li>
                <li><strong>调整列宽</strong>：双击列标题之间的分隔线，自动调整到合适宽度</li>
                <li><strong>设置行高</strong>：选中所有行，右键选择"行高"→"自动调整"</li>
                <li><strong>自动换行</strong>：选中"正确意思"和"您的错误答案"列，右键选择"设置单元格格式"→"对齐"→"自动换行"</li>
            </ol>
        </div>
        
        <div class="action-buttons">
            <a href="index.php" class="btn">返回首页</a>
            <a href="index.php?action=wrong_exam&word_count=10" class="btn secondary">开始错题考查</a>
            <a href="review.php?action=export_wrong_words" class="btn export">导出CSV</a>
            <form method="POST" onsubmit="return confirm('确定要清空所有错题记录吗？');" style="margin: 0; flex: 1;">
                <button type="submit" class="btn danger" style="width: 100%;">清空错题</button>
                <input type="hidden" name="action" value="clear_wrong_words">
            </form>
        </div>
        
        <?php if (empty($wrong_words)): ?>
            <div class="empty-state">
                <h3>暂无错题记录</h3>
                <p>恭喜！您目前没有错题记录。继续保持良好的学习状态！</p>
                <a href="index.php" class="btn">开始新的考查</a>
            </div>
        <?php else: ?>
            <p>您共有 <?php echo count($wrong_words); ?> 条错题记录：</p>
            
            <!-- 修复：添加响应式表格容器 -->
            <div class="table-container">
                <table class="wrong-words-table">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>单词</th>
                            <th>音标</th>
                            <th>正确意思</th>
                            <th>您的错误答案</th>
                            <th>考查方向</th>
                            <th>记录时间</th>
                            <th>单词属性</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wrong_words as $index => $word): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td class="word-cell"><?php echo htmlspecialchars($word['word']); ?></td>
                                <td><?php echo htmlspecialchars($word['phonetic']); ?></td>
                                <td class="meaning-cell"><?php echo htmlspecialchars($word['meaning']); ?></td>
                                <td class="wrong-answer-cell"><?php echo htmlspecialchars($word['wrong_answer']); ?></td>
                                <td><?php echo $word['direction'] === 'en_to_cn' ? '英译中' : '中译英'; ?></td>
                                <td><?php echo htmlspecialchars($word['created_at']); ?></td>
                                <td>
                                    <?php if ($word['is_bold']): ?>
                                        <span class="word-tag tag-bold">黑体</span>
                                    <?php endif; ?>
                                    <?php if (!empty($word['source'])): ?>
                                        <span class="word-tag tag-source"><?php echo htmlspecialchars($word['source']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($word['frequency'])): ?>
                                        <span class="word-tag tag-frequency frequency-<?php echo htmlspecialchars($word['frequency']); ?>">
                                            <?php echo htmlspecialchars($word['frequency']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="action-buttons" style="margin-top: 20px;">
                <a href="index.php?action=wrong_exam&word_count=<?php echo min(count($wrong_words), 10); ?>" class="btn secondary">
                    开始错题考查（<?php echo min(count($wrong_words), 10); ?>题）
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>