<?php
include 'include/db.php';
session_start();

$sn = $_GET['sn'] ?? '';

// ==========================================
// 1. 核心逻辑：从 Session 计算真实得分 (适配 -2 到 2 分制)
// ==========================================
$user_answers = $_SESSION['mbti_answers'] ?? [];
$scores = ['E' => 0, 'I' => 0, 'S' => 0, 'N' => 0, 'T' => 0, 'F' => 0, 'J' => 0, 'P' => 0];

if (!empty($user_answers)) {
    $q_ids = array_keys($user_answers);
    $opt_ids = array_values($user_answers);

    $q_placeholders = implode(',', array_fill(0, count($q_ids), '?'));
    $q_stmt = $db->prepare("SELECT id, dimension, direction FROM questions WHERE id IN ($q_placeholders)");
    $q_stmt->execute($q_ids);
    $questions_info = $q_stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $o_placeholders = implode(',', array_fill(0, count($opt_ids), '?'));
    $o_stmt = $db->prepare("SELECT id, score FROM question_options WHERE id IN ($o_placeholders)");
    $o_stmt->execute($opt_ids);
    $options_info = $o_stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    foreach ($user_answers as $qid => $oid) {
        if (!isset($questions_info[$qid]) || !isset($options_info[$oid])) continue;

        $dimPair = $questions_info[$qid]['dimension']; 
        $direction = strtolower(trim($questions_info[$qid]['direction'])); 
        $point = (int)$options_info[$oid]['score']; 

        $leftKey = $dimPair[0];  
        $rightKey = $dimPair[1];

        if ($direction === 'positive') {
            if ($point > 0) $scores[$leftKey] += $point;
            elseif ($point < 0) $scores[$rightKey] += abs($point);
        } else {
            if ($point > 0) $scores[$rightKey] += $point;
            elseif ($point < 0) $scores[$leftKey] += abs($point);
        }
    }

    $mbti = ($scores['E'] >= $scores['I'] ? 'E' : 'I') .
            ($scores['S'] >= $scores['N'] ? 'S' : 'N') .
            ($scores['T'] >= $scores['F'] ? 'T' : 'F') .
            ($scores['J'] >= $scores['P'] ? 'J' : 'P');

    if ($sn) {
        $update = $db->prepare("UPDATE orders SET mbti_result = ? WHERE order_sn = ?");
        $update->execute([$mbti, $sn]);
    }
    $_SESSION['mbti_scores'] = $scores;
} else {
    $order_check = $db->prepare("SELECT mbti_result FROM orders WHERE order_sn = ?");
    $order_check->execute([$sn]);
    $mbti = $order_check->fetchColumn() ?: 'INTJ';
    $scores = $_SESSION['mbti_scores'] ?? ['E'=>12,'I'=>5,'S'=>4,'N'=>14,'T'=>18,'F'=>3,'J'=>10,'P'=>5];
}

// 获取人格详情
$type_stmt = $db->prepare("SELECT * FROM mbti_types WHERE code = ?");
$type_stmt->execute([$mbti]);
$type = $type_stmt->fetch(PDO::FETCH_ASSOC);

$careers = json_decode($type['careers'] ?? '[]', true);
$compatibility = json_decode($type['compatibility'] ?? '[]', true);
$radar_data = $type['radar_data'] ?? '[70,70,70,70,70,70]';

// ==========================================
// 2. 颜色主题系统 (Radar, Bar, Icon 颜色统一)
// ==========================================
$groups = [
    'analysts'  => ['codes' => 'INTJ INTP ENTJ ENTP', 'bg' => 'bg-[#88619a]', 'light' => 'bg-[#f4f1f5]', 'text' => 'text-[#88619a]', 'hex' => '#88619a', 'rgba' => 'rgba(136, 97, 154, 0.1)'],
    'diplomats' => ['codes' => 'INFJ INFP ENFJ ENFP', 'bg' => 'bg-[#33a474]', 'light' => 'bg-[#f0f7f4]', 'text' => 'text-[#33a474]', 'hex' => '#33a474', 'rgba' => 'rgba(51, 164, 116, 0.1)'],
    'sentinels' => ['codes' => 'ISTJ ISFJ ESTJ ESFJ', 'bg' => 'bg-[#4298b4]', 'light' => 'bg-[#f0f6f8]', 'text' => 'text-[#4298b4]', 'hex' => '#4298b4', 'rgba' => 'rgba(66, 152, 180, 0.1)'],
    'explorers' => ['codes' => 'ISTP ISFP ESTP ESFP', 'bg' => 'bg-[#e4ae3a]', 'light' => 'bg-[#fdf9f0]', 'text' => 'text-[#e4ae3a]', 'hex' => '#e4ae3a', 'rgba' => 'rgba(228, 174, 58, 0.1)'],
];
$activeTheme = $groups['analysts']; 
foreach($groups as $g) { if(strpos($g['codes'], $mbti) !== false) { $activeTheme = $g; break; } }

function calculateDimension($leftKey, $rightKey, $scores) {
    $l = $scores[$leftKey] ?? 0;
    $r = $scores[$rightKey] ?? 0;
    $total = $l + $r;
    if ($total == 0) return ['val' => 50, 'dir' => 'left', 'active' => $leftKey];
    if ($l >= $r) return ['val' => round(($l / $total) * 100), 'dir' => 'left', 'active' => $leftKey];
    else return ['val' => round(($r / $total) * 100), 'dir' => 'right', 'active' => $rightKey];
}

$dimData = [
    'energy'  => calculateDimension('E', 'I', $scores),
    'info'    => calculateDimension('S', 'N', $scores),
    'decide'  => calculateDimension('T', 'F', $scores),
    'tactic'  => calculateDimension('J', 'P', $scores),
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$mbti?> 深度解析报告</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .banner-clip { clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        .bar-transition { transition: width 1s cubic-bezier(0.16, 1, 0.3, 1); }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800">

<?php include 'include/nav.php'; renderNav(); ?>

<div class="<?=$activeTheme['bg']?> banner-clip pt-20 pb-44 px-6 text-white relative overflow-hidden">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between relative z-10">
        <div class="md:w-3/5">
            <h1 class="text-7xl font-black mb-2 italic tracking-tighter"><?=$mbti?></h1>
            <h2 class="text-4xl font-bold mb-6"><?=$type['title']?></h2>
            <p class="text-xl opacity-90 border-l-4 border-white/20 pl-6 italic">"<?=$type['tagline']?>"</p>
        </div>
        <div class="md:w-2/5 flex justify-center mt-10 md:mt-0">
            <img src="assets/images/<?=$mbti?>.svg" class="w-72 h-72 drop-shadow-2xl">
        </div>
    </div>
</div>

<main class="max-w-6xl mx-auto px-6 -mt-24 relative z-20 pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12 border border-gray-100">
                <h3 class="text-2xl font-black mb-12 flex items-center">
                    <span class="w-2 h-8 <?=$activeTheme['bg']?> mr-4 rounded-full"></span>
                    1. 性格维度分析 (Dimensions)
                </h3>
                
                <div class="space-y-12">
                    <?php 
                    $traits = [
                        ['label' => '能量获取', 'l_name' => '外向', 'l_code' => 'E', 'r_name' => '内向', 'r_code' => 'I', 'data' => $dimData['energy']],
                        ['label' => '信息处理', 'l_name' => '实感', 'l_code' => 'S', 'r_name' => '直觉', 'r_code' => 'N', 'data' => $dimData['info']],
                        ['label' => '决策方式', 'l_name' => '思考', 'l_code' => 'T', 'r_name' => '情感', 'r_code' => 'F', 'data' => $dimData['decide']],
                        ['label' => '生活态度', 'l_name' => '判断', 'l_code' => 'J', 'r_name' => '知觉', 'r_code' => 'P', 'data' => $dimData['tactic']],
                    ];

                    foreach($traits as $t): 
                        $d = $t['data'];
                        $isLeft = ($d['dir'] == 'left');
                    ?>
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-xl font-bold <?= $isLeft ? $activeTheme['text'] : 'text-gray-300' ?>">
                                <?=$t['l_name']?> (<?=$t['l_code']?>)
                            </div>
                            <div class="text-xs font-black text-gray-400 uppercase tracking-widest"><?=$t['label']?></div>
                            <div class="text-xl font-bold <?= !$isLeft ? $activeTheme['text'] : 'text-gray-300' ?>">
                                <?=$t['r_name']?> (<?=$t['r_code']?>)
                            </div>
                        </div>
                        
                        <div class="h-4 w-full bg-gray-100 rounded-full flex <?= $isLeft ? 'justify-start' : 'justify-end' ?> overflow-hidden p-1">
                            <div class="h-full rounded-full <?=$activeTheme['bg']?> bar-transition shadow-sm flex items-center justify-center text-[10px] text-white font-bold" 
                                 style="width: <?=$d['val']?>%">
                                 <?=$d['val']?>%
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
                <h3 class="text-2xl font-black mb-8 flex items-center">
                    <span class="w-2 h-8 <?=$activeTheme['bg']?> mr-4 rounded-full"></span>
                    2. 人格画像解析 (Profile)
                </h3>
                <p class="text-gray-600 leading-relaxed text-lg mb-8"><?=$type['description']?></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-6 bg-rose-50 rounded-2xl border-l-4 border-rose-400">
                        <h4 class="font-bold text-rose-700 mb-2">潜在风险</h4>
                        <p class="text-sm text-gray-500"><?=$type['risk_warning'] ?? '暂无数据'?></p>
                    </div>
                    <div class="p-6 bg-emerald-50 rounded-2xl border-l-4 border-emerald-400">
                        <h4 class="font-bold text-emerald-700 mb-2">成长建议</h4>
                        <p class="text-sm text-gray-500"><?=$compatibility['advice'] ?? '暂无数据'?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
                <h3 class="text-2xl font-black mb-8 flex items-center">
                    <span class="w-2 h-8 <?=$activeTheme['bg']?> mr-4 rounded-full"></span>
                    3. 黄金职业赛道 (Careers)
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach($careers as $c): ?>
                    <div class="p-4 <?=$activeTheme['light']?> rounded-2xl text-center border border-gray-50 hover:shadow-lg transition-all">
                        <span class="text-2xl block mb-2">💼</span>
                        <div class="font-bold text-gray-700 text-sm"><?=$c?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                <h4 class="font-black text-center mb-6">核心能力雷达</h4>
                <canvas id="radarChart"></canvas>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                <h4 class="font-black mb-6 flex items-center">
                    <span class="w-2 h-5 <?=$activeTheme['bg']?> mr-3 rounded-full"></span>
                    人际伴侣 (Compatibility)
                </h4>
                <div class="space-y-6">
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">最佳契合</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach(($compatibility['best'] ?? []) as $b): ?>
                            <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-xs font-black"><?=$b?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">可能冲突</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach(($compatibility['warning'] ?? []) as $w): ?>
                            <span class="px-4 py-1.5 bg-rose-50 text-rose-600 rounded-full text-xs font-black"><?=$w?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <button onclick="window.print()" class="w-full bg-gray-900 text-white py-5 rounded-2xl font-bold shadow-xl hover:bg-black transition-all">
                导出报告 PDF
            </button>
        </div>
    </div>
</main>

<script>
// 雷达图：完全匹配人格主题颜色
const radarCtx = document.getElementById('radarChart').getContext('2d');
new Chart(radarCtx, {
    type: 'radar',
    data: {
        labels: ['决策', '感召', '逻辑', '创意', '执行', '洞察'],
        datasets: [{
            label: '能力指数',
            data: <?=$radar_data?>,
            backgroundColor: '<?=$activeTheme["rgba"]?>',
            borderColor: '<?=$activeTheme["hex"]?>',
            borderWidth: 3,
            pointBackgroundColor: '<?=$activeTheme["hex"]?>',
            pointRadius: 4
        }]
    },
    options: {
        scales: {
            r: {
                min: 0, 
                max: 100,
                ticks: { display: false },
                grid: { color: '#f1f5f9' },
                angleLines: { color: '#f1f5f9' }
            }
        },
        plugins: { legend: { display: false } }
    }
});
</script>

</body>
</html>