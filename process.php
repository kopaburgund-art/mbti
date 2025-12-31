<?php
include 'include/db.php';
session_start();

// ==========================================
// 1. 接收答案逻辑 (POST 请求)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q_id = (int)$_POST['q_id'];
    $opt_id = (int)$_POST['option_id']; // 注意：index.php 传的是 option_id
    $next_p = (int)$_POST['next_p'];
    
    // 记录答案：存入选项的 ID
    $_SESSION['mbti_answers'][$q_id] = $opt_id;
    
    header("Location: index.php?p=$next_p");
    exit;
}

// ==========================================
// 2. 答题完成后的计算逻辑 (GET 请求)
// ==========================================
if (isset($_GET['finish'])) {
    
    $answers = $_SESSION['mbti_answers'] ?? [];
    
    if (empty($answers)) {
        header("Location: index.php");
        exit;
    }

    // 初始化 8 个维度的总分
    $scores = ['E'=>0,'I'=>0,'S'=>0,'N'=>0,'T'=>0,'F'=>0,'J'=>0,'P'=>0];

    // 获取所有题目和选项的得分配置
    // 这里用 IN 查询一次性查出所有答过的题目信息，效率更高
    $q_ids = array_keys($answers);
    $placeholders = implode(',', array_fill(0, count($q_ids), '?'));
    
    // 查询题目对应的维度和方向
    $stmt_q = $db->prepare("SELECT id, dimension, direction FROM questions WHERE id IN ($placeholders)");
    $stmt_q->execute($q_ids);
    $questions_info = $stmt_q->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    // 查询用户选中的选项得分
    $opt_ids = array_values($answers);
    $opt_placeholders = implode(',', array_fill(0, count($opt_ids), '?'));
    $stmt_o = $db->prepare("SELECT id, score FROM question_options WHERE id IN ($opt_placeholders)");
    $stmt_o->execute($opt_ids);
    $options_info = $stmt_o->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    // 评分迭代
// process.php 核心循环
foreach ($answers as $qid => $oid) {
    if (!isset($questions_info[$qid]) || !isset($options_info[$oid])) continue;

    $dimPair = $questions_info[$qid]['dimension']; 
    $direction = strtolower(trim($questions_info[$qid]['direction'])); 
    $point = (int)$options_info[$oid]['score']; // 这里是 2, 1, 0, -1, -2

    $leftKey = $dimPair[0];  // 比如 E
    $rightKey = $dimPair[1]; // 比如 I

    if ($direction === 'positive') {
        // 正向：正分给左，负分给右
        if ($point > 0) $scores[$leftKey] += $point;
        elseif ($point < 0) $scores[$rightKey] += abs($point);
    } else {
        // 反向：正分给右，负分给左
        if ($point > 0) $scores[$rightKey] += $point;
        elseif ($point < 0) $scores[$leftKey] += abs($point);
    }
}

    // 确定性格类型 (MBTI 4位代码)
    $mbti = ($scores['E'] >= $scores['I'] ? 'E' : 'I') .
            ($scores['S'] >= $scores['N'] ? 'S' : 'N') .
            ($scores['T'] >= $scores['F'] ? 'T' : 'F') .
            ($scores['J'] >= $scores['P'] ? 'J' : 'P');

    // 将计算好的详细分数存入 Session，供 result.php 渲染进度条使用
    $_SESSION['mbti_scores'] = $scores;

    // 3. 创建订单
    $sn = 'MB' . time();
    $stmt = $db->prepare("INSERT INTO orders (order_sn, mbti_result) VALUES (?, ?)");
    $stmt->execute([$sn, $mbti]);

    header("Location: payment.php?sn=$sn");
    exit;
}

header("Location: index.php");
exit;