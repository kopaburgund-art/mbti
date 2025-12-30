<?php
session_start();
$db = new PDO('sqlite:db/mbti_pro.sqlite');

// 1. 接收答案逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['mbti_answers'][(int)$_POST['q_id']] = $_POST['ans'];
    header("Location: index.php");
    exit;
}

// 2. 答题完成后的计算逻辑
$answers = $_SESSION['mbti_answers'] ?? [];
$all_qs = $db->query("SELECT * FROM questions")->fetchAll(PDO::FETCH_ASSOC);

$scores = ['E'=>0,'I'=>0,'S'=>0,'N'=>0,'T'=>0,'F'=>0,'J'=>0,'P'=>0];
foreach($all_qs as $q) {
    $user_ans = $answers[$q['id']];
    $dimA = $q['dimension'][0]; $dimB = $q['dimension'][1];
    $user_ans === 'A' ? $scores[$dimA]++ : $scores[$dimB]++;
}

$mbti = ($scores['E']>=$scores['I']?'E':'I').($scores['S']>=$scores['N']?'S':'N').($scores['T']>=$scores['F']?'T':'F').($scores['J']>=$scores['P']?'J':'P');

// 3. 创建未支付订单
$sn = 'MB' . time();
$stmt = $db->prepare("INSERT INTO orders (order_sn, mbti_result) VALUES (?, ?)");
$stmt->execute([$sn, $mbti]);

header("Location: payment.php?sn=$sn");