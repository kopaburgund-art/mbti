<?php
session_start();
// 确保数据库路径正确
$db = new PDO('sqlite:db/mbti_pro.sqlite');

// 1. 接收答案逻辑 (POST 请求)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q_id = (int)$_POST['q_id'];
    $next_p = (int)$_POST['next_p'];
    
    // 记录答案：使用题目 ID 作为键，确保重复提交时是覆盖而不是追加
    $_SESSION['mbti_answers'][$q_id] = $_POST['ans'];
    
    // 正常跳转到下一题
    header("Location: index.php?p=$next_p");
    exit;
}

// 2. 答题完成后的计算逻辑 (GET 请求，由 ?finish=1 触发)
// 这里统一使用 finish，请确保 index.php 里的跳转也是 process.php?finish=1
if (isset($_GET['finish']) || isset($_GET['calc'])) {
    
    $answers = $_SESSION['mbti_answers'] ?? [];
    $all_qs = $db->query("SELECT * FROM questions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    
    // 防错检查：如果用户一个题都没答就闯入这里，送回首页
    if (empty($answers)) {
        header("Location: index.php");
        exit;
    }

    $scores = ['E'=>0,'I'=>0,'S'=>0,'N'=>0,'T'=>0,'F'=>0,'J'=>0,'P'=>0];
    
    foreach($all_qs as $q) {
        $qid = $q['id'];
        // 关键修复：检查该题是否有答案，防止报错
        if (isset($answers[$qid])) {
            $user_ans = $answers[$qid];
            $dimA = $q['dimension'][0]; 
            $dimB = $q['dimension'][1];
            $user_ans === 'A' ? $scores[$dimA]++ : $scores[$dimB]++;
        }
    }

    // 确定性格类型
    $mbti = ($scores['E'] >= $scores['I'] ? 'E' : 'I') .
            ($scores['S'] >= $scores['N'] ? 'S' : 'N') .
            ($scores['T'] >= $scores['F'] ? 'T' : 'F') .
            ($scores['J'] >= $scores['P'] ? 'J' : 'P');

    // 3. 创建订单
    $sn = 'MB' . time();
    $stmt = $db->prepare("INSERT INTO orders (order_sn, mbti_result) VALUES (?, ?)");
    $stmt->execute([$sn, $mbti]);

    // 计算完直接去支付页，不要再回 index.php
    header("Location: payment.php?sn=$sn");
    exit;
}

// 如果既不是 POST 也不是完成指令，回退到首页
header("Location: index.php");
exit;