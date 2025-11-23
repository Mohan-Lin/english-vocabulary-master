<?php
// 检查是否已安装
if (!file_exists('config.php')) {
    header('Location: install.php');
    exit;
}
require_once 'functions.php';

// 获取所有来源选项
$all_sources = get_all_sources();

// 处理错题考查请求
if (isset($_GET['action']) && $_GET['action'] === 'wrong_exam') {
    try {
        // 获取用户选择的题目数量
        $word_count = isset($_GET['word_count']) ? (int)$_GET['word_count'] : 10;
        
        // 验证数量
        if ($word_count < 1) {
            $word_count = 1;
        } elseif ($word_count > 100) {
            $word_count = 100;
        }
        
        // 获取错题单词
        $wrong_words = get_wrong_words_for_exam($word_count);
        
        if (empty($wrong_words)) {
            $error = "暂无错题记录，无法开始错题考查";
        } else {
            // 存储到session
            session_start();
            $_SESSION['exam_words'] = $wrong_words;
            $_SESSION['exam_direction'] = 'mixed'; // 错题考查默认使用混合模式
            $_SESSION['current_index'] = 0;
            $_SESSION['answers'] = [];
            $_SESSION['use_ai'] = false; // 错题考查默认不启用AI
            $_SESSION['show_answer'] = 'after_each'; // 每题后显示答案
            $_SESSION['is_wrong_exam'] = true; // 标记为错题考查
            
            // 重定向到考试页面
            header('Location: exam.php');
            exit;
        }
    } catch (Exception $e) {
        $error = "获取错题失败: " . $e->getMessage();
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word_count = (int)$_POST['word_count'];
    $direction = $_POST['direction'];
    $use_ai = isset($_POST['use_ai']) ? true : false;
    $show_answer = $_POST['show_answer'] ?? 'after_each'; // 默认每题后显示
    
    // 新的过滤条件
    $source = $_POST['source'] ?? 'all';
    $is_bold = $_POST['is_bold'] ?? 'all';
    $frequency = $_POST['frequency'] ?? 'all';
    
    // 验证输入
    if ($word_count < 1 || $word_count > 100) {
        $error = "单词数量必须在1-100之间";
    } else {
        // 构建过滤条件
        $filters = [
            'source' => $source,
            'is_bold' => $is_bold,
            'frequency' => $frequency
        ];
        
        // 获取过滤后的单词
        $words = get_filtered_words($word_count, $filters);
        
        if (empty($words)) {
            $error = "根据您选择的条件，没有找到匹配的单词";
        } else {
            // 存储到session
            session_start();
            $_SESSION['exam_words'] = $words;
            $_SESSION['exam_direction'] = $direction;
            $_SESSION['current_index'] = 0;
            $_SESSION['answers'] = [];
            $_SESSION['use_ai'] = $use_ai;
            $_SESSION['show_answer'] = $show_answer;
            $_SESSION['is_wrong_exam'] = false; // 标记为普通考查
            $_SESSION['filters'] = $filters; // 保存过滤条件
            
            // 重定向到考试页面
            header('Location: exam.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>高考英语3500词汇测试 - 高中英语词汇量测试系统</title>
    <meta name="description" content="免费在线高考英语3500词汇测试系统，专业的高中英语词汇量测试平台，提供高考英语词汇测试、单词记忆、错题复习功能，帮助高中生高效掌握高考英语3500词汇">
    <meta name="keywords" content="高考英语3500词汇测试,高考英语词汇测试,高中英语词汇量测试,高考英语单词测试,高中英语单词,英语词汇测试,高考词汇">
    <meta name="author" content="高中英语单词考查系统">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <link rel="stylesheet" href="css/style.css">
    <link rel="canonical" href="https://en.linmohan.top">
    <style>
    /* SEO内容折叠样式 */
    .seo-section {
        margin-top: 40px;
        background-color: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    
    .seo-toggle {
        padding: 15px 20px;
        background-color: #e9ecef;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #495057;
        transition: background-color 0.3s ease;
    }
    
    .seo-toggle:hover {
        background-color: #dee2e6;
    }
    
    .seo-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }
    
    .seo-content.expanded {
        padding: 20px;
        max-height: 2000px;
    }
    
    /* 新添加的样式 */
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        border: 1px solid #e9ecef;
    }
    
    .filter-section h3 {
        margin-top: 0;
        color: #495057;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    
    .filter-group {
        margin-bottom: 15px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #495057;
    }
    
    .select-wrapper select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 16px;
        background-color: #fff;
    }
    
    .radio-group.filter-options {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 8px;
    }
    
    .radio-group.filter-options label {
        display: flex;
        align-items: center;
        margin: 0;
        font-weight: normal;
        cursor: pointer;
    }
    
    .radio-group.filter-options input[type="radio"] {
        margin-right: 5px;
    }
    
    .exam-form {
        background-color: #fff;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>高中英语单词考查系统</h1>
            <p class="site-description">专业的高考英语词汇学习与测试平台，帮助您高效掌握3500+高考核心词汇</p>
        </header>
        
        <section class="exam-form">
            <h2>开始您的单词考查</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="word_count">每次考查单词数量：</label>
                    <input type="number" id="word_count" name="word_count" min="1" max="100" value="10" required>
                </div>
                
                <div class="form-group">
                    <label>考查方向：</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="direction" value="en_to_cn" checked>
                            英译中
                        </label>
                        <label>
                            <input type="radio" name="direction" value="cn_to_en">
                            中译英
                        </label>
                        <label>
                            <input type="radio" name="direction" value="mixed">
                            混合模式
                        </label>
                    </div>
                </div>
                
                <!-- 新添加的过滤条件 -->
                <div class="filter-section">
                    <h3>📚 测试范围筛选</h3>
                    
                    <div class="filter-group">
                        <label for="source">选择教材单元：</label>
                        <div class="select-wrapper">
                            <select id="source" name="source">
                                <option value="all">全部单元</option>
                                <?php foreach ($all_sources as $source): ?>
                                    <option value="<?php echo htmlspecialchars($source); ?>"><?php echo htmlspecialchars($source); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>黑体字单词：</label>
                        <div class="radio-group filter-options">
                            <label>
                                <input type="radio" name="is_bold" value="all" checked>
                                全部单词
                            </label>
                            <label>
                                <input type="radio" name="is_bold" value="yes">
                                只测黑体字
                            </label>
                            <label>
                                <input type="radio" name="is_bold" value="no">
                                不测黑体字
                            </label>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>考查频率：</label>
                        <div class="radio-group filter-options">
                            <label>
                                <input type="radio" name="frequency" value="all" checked>
                                全部频率
                            </label>
                            <label>
                                <input type="radio" name="frequency" value="超高频">
                                超高频
                            </label>
                            <label>
                                <input type="radio" name="frequency" value="高频">
                                高频
                            </label>
                            <label>
                                <input type="radio" name="frequency" value="中频">
                                中频
                            </label>
                            <label>
                                <input type="radio" name="frequency" value="低频">
                                低频
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="use_ai" value="1">
                        启用AI分析
                    </label>
                    <small>使用AI对答案进行更精准的分析</small>
                </div>
                
                <div class="form-group">
                    <label>显示正确答案：</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="show_answer" value="after_each" checked>
                            每题后立即显示
                        </label>
                        <label>
                            <input type="radio" name="show_answer" value="after_all">
                            全部完成后显示
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn primary">开始考查</button>
                </div>
            </form>
        </section>
        
        <section class="wrong-exam-section">
            <h2>错题专项练习</h2>
            <div class="wrong-exam-info">
                <p>错题专项练习将从您的错题库中随机抽取单词进行考查，帮助您针对性强化薄弱词汇。答对的题目将从错题列表中自动删除。</p>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="GET" action="index.php" class="wrong-exam-form">
                    <input type="hidden" name="action" value="wrong_exam">
                    
                    <div class="form-group">
                        <label for="wrong_word_count">考查题目数量：</label>
                        <input type="number" id="wrong_word_count" name="word_count" min="1" max="100" value="10" required>
                    </div>
                    
                    <div class="btn-container">
                        <button type="submit" class="btn secondary">开始错题考查</button>
                        <a href="review.php" class="btn">查看错题列表</a>
                    </div>
                </form>
            </div>
        </section>
        
        <!-- SEO内容区域 - 默认折叠，放在最下方 -->
        <div class="seo-section">
            <div class="seo-toggle" onclick="toggleSEOContent()">
                <span>📋 查看详细介绍</span>
                <span id="seo-arrow">▼</span>
            </div>
            <div class="seo-content" id="seo-content">
                <div class="features">
                    <h2>为什么选择我们的单词考查系统？</h2>
                    <div class="feature-grid">
                        <div class="feature">
                            <div class="icon">📚</div>
                            <h3>完整高考词汇库</h3>
                            <p>收录最新高考大纲要求的3500+核心词汇，包含音标、释义和例句</p>
                        </div>
                        <div class="feature">
                            <div class="icon">🔍</div>
                            <h3>智能AI分析</h3>
                            <p>使用先进AI技术分析您的答案，提供精准反馈和学习建议</p>
                        </div>
                        <div class="feature">
                            <div class="icon">📊</div>
                            <h3>学习进度追踪</h3>
                            <p>详细记录您的学习数据，生成可视化报告，帮助您查漏补缺</p>
                        </div>
                        <div class="feature">
                            <div class="icon">🔄</div>
                            <h3>错题智能复习</h3>
                            <p>自动收集错题，生成个性化复习计划，针对性强化薄弱词汇</p>
                        </div>
                    </div>
                </div>
                
                <div class="how-it-works">
                    <h2>如何使用本系统？</h2>
                    <ol>
                        <li><strong>选择考查设置</strong> - 自定义单词数量、考查方向和难度</li>
                        <li><strong>开始单词测试</strong> - 根据提示输入单词或中文意思</li>
                        <li><strong>查看即时反馈</strong> - 系统提供答案正确性分析和学习建议</li>
                        <li><strong>复习错题</strong> - 系统自动收集错题，供您针对性复习</li>
                        <li><strong>定期测试</strong> - 每周测试，跟踪您的词汇量增长</li>
                    </ol>
                </div>
                
                <div class="testimonials">
                    <h2>学生反馈</h2>
                    <div class="testimonial">
                        <blockquote>
                            "使用这个系统后，我的词汇量在三个月内从2000提升到3500，高考英语提高了25分！"
                        </blockquote>
                        <div class="author">- 张同学，北京四中</div>
                    </div>
                    <div class="testimonial">
                        <blockquote>
                            "错题复习功能太实用了，让我能集中攻克不熟悉的单词，效率提升了好几倍。"
                        </blockquote>
                        <div class="author">- 李同学，上海中学</div>
                    </div>
                </div>
                
                <div class="guide-content">
                    <h2>高考英语词汇学习指南</h2>
                    <p>掌握高考英语词汇是取得高分的关键。根据教育部最新大纲，高考英语要求掌握约3500个单词和400-500个短语。我们的系统将这些词汇分为高频、中频和低频三个等级，帮助您优先掌握最重要的词汇。</p>
                    
                    <h3>高效记忆方法：</h3>
                    <ul>
                        <li><strong>词根词缀法</strong> - 通过了解单词构成规律记忆</li>
                        <li><strong>联想记忆法</strong> - 将新单词与已知事物关联</li>
                        <li><strong>语境记忆法</strong> - 在句子和文章中学习单词用法</li>
                        <li><strong>间隔重复法</strong> - 科学安排复习时间点</li>
                    </ul>
                    
                    <h3>高考词汇分类：</h3>
                    <table class="vocab-table">
                        <thead>
                            <tr>
                                <th>词汇类型</th>
                                <th>数量</th>
                                <th>重要性</th>
                                <th>建议学习时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>高频词汇</td>
                                <td>800词</td>
                                <td>★★★★★</td>
                                <td>优先掌握</td>
                            </tr>
                            <tr>
                                <td>中频词汇</td>
                                <td>1500词</td>
                                <td>★★★★</td>
                                <td>重点学习</td>
                            </tr>
                            <tr>
                                <td>低频词汇</td>
                                <td>1200词</td>
                                <td>★★★</td>
                                <td>了解即可</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> 高中英语单词考查系统 | 联系我们: admin@shaoyunb.top</p>
            <nav>
                <a href="http://www.linmohan.top">关于我们</a> | 
                <a href="http://www.070912.xyz">博客</a> | 
                <a href="http://www.9876111.xyz">防失联页</a>
            </nav>
        </footer>
    </div>
    
    <script>
    // SEO内容折叠功能
    function toggleSEOContent() {
        const content = document.getElementById('seo-content');
        const arrow = document.getElementById('seo-arrow');
        
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            arrow.textContent = '▼';
        } else {
            content.classList.add('expanded');
            arrow.textContent = '▲';
        }
    }
    
    // 页面加载时的SEO优化
    document.addEventListener('DOMContentLoaded', function() {
        // 为重要链接添加适当的rel属性
        const links = document.querySelectorAll('a[href^="http"]:not([href*="' + window.location.hostname + '"])');
        links.forEach(link => {
            if (!link.hasAttribute('rel')) {
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
        
        // 添加表单验证
        const form = document.querySelector('form[method="POST"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const wordCount = document.getElementById('word_count').value;
                if (wordCount < 1 || wordCount > 100) {
                    alert('单词数量必须在1-100之间');
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        }
    });
    </script>
</body>
</html>