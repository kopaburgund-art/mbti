<?php
session_start();
$db = new PDO('sqlite:db/mbti_pro.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$all_qs = $db->query("SELECT * FROM questions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$total = count($all_qs);

if ($total === 0) {
    die("Error: 数据库中没有题目，请先运行 setup.php 初始化。");
}

if (isset($_GET['reset'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$answers = $_SESSION['mbti_answers'] ?? [];
$current_index = isset($_GET['p']) ? (int)$_GET['p'] : count($answers);

if ($current_index >= $total) {
    header("Location: process.php?finish=1");
    exit;
}

$current_q = $all_qs[$current_index];
$progress = round(($current_index / $total) * 100);

/* ===== 拉当前题目的选项 ===== */
$stmt = $db->prepare("
    SELECT * FROM question_options
    WHERE question_id = ?
    ORDER BY score DESC
");
$stmt->execute([$current_q['id']]);
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>MBTI 深度性格测评 · 专业版</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1f2937;
        }
        .banner-clip {
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
    </style>
</head>

<body>

<!-- ===================== Nav（原样保留） ===================== -->
<header class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="font-black text-xl tracking-tight text-indigo-600">
            MBTI PRO
        </div>
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-500">
            <a href="index.php" class="text-indigo-600 font-bold">性格测试</a>
            <a href="#" class="hover:text-indigo-600 transition">MBTI 介绍</a>
            <a href="#" class="hover:text-indigo-600 transition">类型说明</a>
            <a href="#" class="hover:text-indigo-600 transition">专业版</a>
        </nav>
    </div>
</header>

<!-- ===================== Banner（原样保留） ===================== -->
<section class="bg-indigo-600 banner-clip pt-20 pb-32 px-6 text-white relative overflow-hidden">
    <div class="max-w-5xl mx-auto relative z-10">
        <span class="uppercase tracking-widest text-sm opacity-80">
            专业心理测评系统
        </span>
        <h1 class="text-4xl md:text-5xl font-black mt-4 mb-6">
            MBTI 深度性格测试
        </h1>
        <p class="max-w-2xl text-lg opacity-90 leading-relaxed">
            基于荣格心理类型理论，结合行为倾向与认知偏好，生成可执行的人格分析报告。
        </p>
    </div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 -rotate-12 translate-x-32 -translate-y-32"></div>
</section>

<!-- ===================== 主体 ===================== -->
<main class="max-w-3xl mx-auto px-6 -mt-20 relative z-20">

    <!-- 进度卡 -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <div class="flex justify-between text-sm font-bold text-gray-400 mb-2">
            <span>测试进度</span>
            <span><?= $current_index + 1 ?> / <?= $total ?></span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-indigo-600 transition-all duration-500"
                 style="width: <?= $progress ?>%"></div>
        </div>
    </div>

    <!-- 题目卡 -->
    <div class="bg-white rounded-2xl shadow-xl p-10 md:p-14 mb-12">

        <form action="process.php" method="POST">
            <input type="hidden" name="q_id" value="<?= $current_q['id'] ?>">
            <input type="hidden" name="next_p" value="<?= $current_index + 1 ?>">

            <h2 class="text-2xl md:text-3xl font-bold leading-snug mb-12">
                <?= htmlspecialchars($current_q['question_text']) ?>
            </h2>

            <div class="space-y-4">
                <?php foreach ($options as $opt): ?>
                    <button type="submit"
                            name="option_id"
                            value="<?= $opt['id'] ?>"
                            class="group w-full text-left p-6 rounded-xl border-2 border-gray-100
                                   hover:border-indigo-500 hover:bg-indigo-50 transition flex justify-between items-center">
                        <span class="text-gray-700 font-medium group-hover:text-indigo-700">
                            <?= htmlspecialchars($opt['label']) ?>
                        </span>
                        <span class="opacity-0 group-hover:opacity-100 text-indigo-500">→</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </form>

        <div class="flex justify-between items-center mt-12 pt-6 border-t border-gray-50">
            <?php if ($current_index > 0): ?>
                <a href="?p=<?= $current_index - 1 ?>"
                   class="text-sm font-bold text-gray-400 hover:text-indigo-600 transition">
                    ← 上一题
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <a href="?reset=1"
               class="text-xs text-gray-300 hover:text-red-400 transition">
                重置测试
            </a>
        </div>
    </div>
</main>

<footer class="py-16 text-center text-sm text-gray-400">
    © 2025 MBTI 性格研究实验室 · 专业心理评估系统
</footer>

</body>
</html>
