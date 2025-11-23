<?php
session_start();
require_once 'functions.php';
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);
// 检查会话是否有效
if (!isset($_SESSION['exam_words']) || !isset($_SESSION['exam_direction'])) {
    echo json_encode(['status' => 'error', 'message' => '会话已过期或无效']);
    exit;
}
// 获取参数并进行安全验证
$word_id = isset($_POST['word_id']) ? (int)$_POST['word_id'] : 0;
$user_answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';
$direction = isset($_POST['direction']) ? $_POST['direction'] : '';
$current_index = isset($_POST['current_index']) ? (int)$_POST['current_index'] : 0;
// 验证基本参数
if ($word_id <= 0 || empty($direction) || $current_index < 0) {
    echo json_encode([
        'status' => 'error', 
        'message' => '无效的请求参数',
        'debug' => [
            'word_id' => $word_id,
            'user_answer' => $user_answer,
            'direction' => $direction,
            'current_index' => $current_index
        ]
    ]);
    exit;
}
// 从会话中获取数据
$words = $_SESSION['exam_words'];
$answers = $_SESSION['answers'];
$use_ai = isset($_SESSION['use_ai']) ? $_SESSION['use_ai'] : false;
$show_answer = isset($_SESSION['show_answer']) ? $_SESSION['show_answer'] : 'after_each';
$is_wrong_exam = isset($_SESSION['is_wrong_exam']) ? $_SESSION['is_wrong_exam'] : false;
// 验证当前索引是否有效
if ($current_index >= count($words)) {
    echo json_encode([
        'status' => 'error', 
        'message' => '无效的题目索引',
        'debug' => [
            'current_index' => $current_index,
            'word_count' => count($words)
        ]
    ]);
    exit;
}
// 查找当前单词
$current_word = null;
foreach ($words as $word) {
    if (isset($word['id']) && $word['id'] == $word_id) {
        $current_word = $word;
        break;
    }
}
if (!$current_word) {
    echo json_encode([
        'status' => 'error', 
        'message' => '单词不存在',
        'debug' => [
            'word_id' => $word_id,
            'words' => $words
        ]
    ]);
    exit;
}
try {
    $is_correct = false;
    $ai_feedback = null;
    $deleted = false;
    
    // 处理"不知道"的情况
    if ($user_answer === 'dont_know') {
        $user_answer = '不知道';
        $is_correct = false;
    } else {
        // 检查答案
        $check_result = check_answer(
            ($direction === 'en_to_cn') ? $current_word['meaning'] : $current_word['word'],
            $user_answer,
            $direction,
            $use_ai,
            $current_word
        );
        
        $is_correct = $check_result['result'];
        $ai_feedback = $check_result['ai_feedback'];
    }
    
    // 如果是错题考查并且答对了，删除对应的错题记录
    if ($is_wrong_exam && $is_correct) {
        $deleted_rows = delete_wrong_word_by_word_id($word_id);
        $deleted = ($deleted_rows > 0);
        error_log("错题考查答对，删除错题记录: word_id={$word_id}, 删除行数: {$deleted_rows}");
    }
    
    // 记录答案
    $answers[$current_index] = [
        'user_answer' => $user_answer,
        'is_correct' => $is_correct,
        'direction' => $direction,
        'ai_feedback' => $ai_feedback,
        'deleted' => $deleted // 记录是否删除了错题
    ];
    
    // 记录错题（只有在普通考查模式下才记录）
    if (!$is_correct && !$is_wrong_exam) {
        $user_ip = get_user_ip();
        record_wrong_word($current_word['id'], $user_ip, $user_answer, $direction);
    }
    
    // 更新session
    $_SESSION['current_index'] = $current_index + 1;
    $_SESSION['answers'] = $answers;
    
    // 准备响应数据
    $response = [
        'status' => 'success',
        'is_correct' => $is_correct,
        'user_answer' => $user_answer,
        'correct_answer' => ($direction === 'en_to_cn') ? 
            $current_word['meaning'] : 
            $current_word['word'],
        'ai_feedback' => $ai_feedback ? $ai_feedback : '',
        'show_answer' => $show_answer,
        'is_wrong_exam' => $is_wrong_exam,
        'deleted' => $deleted // 返回是否删除了错题
    ];
    
    // 如果删除了错题，添加成功提示
    if ($deleted) {
        $response['success_message'] = '恭喜！这道题已经从错题列表中移除。';
    }
    
    // 确定重定向路径
    if (($current_index + 1) < count($words)) {
        $response['redirect'] = 'exam.php';
    } else {
        $response['redirect'] = 'result.php';
    }
    
    // 设置正确的Content-Type头
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 记录错误日志
    error_log('submit_answer.php 错误: ' . $e->getMessage());
    
    // 设置正确的Content-Type头
    header('Content-Type: application/json; charset=utf-8');
    
    // 返回错误响应
    echo json_encode([
        'status' => 'error',
        'message' => '服务器处理请求时出错',
        'error_details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>