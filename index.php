<?php
session_start();
$db = new PDO('sqlite:db/mbti_pro.sqlite');
$all_qs = $db->query("SELECT * FROM questions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$total = count($all_qs);

// 如果用户点击了“重新开始”
if(isset($_GET['reset'])) { session_destroy(); header("Location: index.php"); exit; }

// 获取当前进度
$answers = $_SESSION['mbti_answers'] ?? [];
$current_index = count($answers);

// 逻辑控制：如果答完了，直接去处理结果
if ($current_index >= $total) { header("Location: process.php"); exit; }

$current_q = $all_qs[$current_index];
$progress = round(($current_index / $total) * 100);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>MBTI 深度性格测评</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #020617; color: white; font-family: sans-serif; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
        .step-enter { animation: slideIn 0.4s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">
    <div class="max-w-xl w-full">
        <div class="mb-10 text-center">
            <div class="h-1.5 w-full bg-white/5 rounded-full mb-4">
                <div class="h-full bg-indigo-500 transition-all duration-700 shadow-[0_0_15px_#6366f1]" style="width: <?=$progress?>%"></div>
            </div>
            <span class="text-xs font-mono text-indigo-400 tracking-widest">进度: <?=$progress?>% / 深度扫描中</span>
        </div>

        <div class="glass p-10 rounded-[2.5rem] step-enter relative overflow-hidden">
            <form action="process.php" method="POST" id="qForm">
                <input type="hidden" name="q_id" value="<?=$current_q['id']?>">
                <h2 class="text-2xl md:text-3xl font-light leading-relaxed mb-12 text-gray-100 italic">
                    " <?=$current_q['question_text'] ?> "
                </h2>
                <div class="grid gap-4">
                    <button type="submit" name="ans" value="A" class="group text-left p-6 rounded-2xl border border-white/5 bg-white/5 hover:bg-indigo-600/20 hover:border-indigo-500/50 transition-all">
                        <span class="text-gray-400 group-hover:text-white"><?=$current_q['option_a']?></span>
                    </button>
                    <button type="submit" name="ans" value="B" class="group text-left p-6 rounded-2xl border border-white/5 bg-white/5 hover:bg-indigo-600/20 hover:border-indigo-500/50 transition-all">
                        <span class="text-gray-400 group-hover:text-white"><?=$current_q['option_b']?></span>
                    </button>
                </div>
            </form>
            <a href="?reset=1" class="block text-center mt-8 text-xs text-gray-600 hover:text-gray-400 transition-colors">放弃进度并重新开始</a>
        </div>
    </div>
</body>
</html>