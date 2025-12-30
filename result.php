<?php
session_start();
$sn = $_GET['sn'] ?? '';
$db = new PDO('sqlite:db/mbti_pro.sqlite');

$order = $db->query("SELECT * FROM orders WHERE order_sn = '$sn'")->fetch(PDO::FETCH_ASSOC);
$mbti = $order['mbti_result'] ?? 'INTJ';
$type = $db->query("SELECT * FROM mbti_types WHERE code = '$mbti'")->fetch(PDO::FETCH_ASSOC);

// --- 阵营视觉系统映射 ---
$groups = [
    'analysts'  => ['codes' => 'INTJ INTP ENTJ ENTP', 'bg' => 'bg-[#88619a]', 'light' => 'bg-[#f4f1f5]', 'text' => 'text-[#88619a]'],
    'diplomats' => ['codes' => 'INFJ INFP ENFJ ENFP', 'bg' => 'bg-[#33a474]', 'light' => 'bg-[#f0f7f4]', 'text' => 'text-[#33a474]'],
    'sentinels' => ['codes' => 'ISTJ ISFJ ESTJ ESFJ', 'bg' => 'bg-[#4298b4]', 'light' => 'bg-[#f0f6f8]', 'text' => 'text-[#4298b4]'],
    'explorers' => ['codes' => 'ISTP ISFP ESTP ESFP', 'bg' => 'bg-[#e4ae3a]', 'light' => 'bg-[#fdf9f0]', 'text' => 'text-[#e4ae3a]'],
];

$activeTheme = $groups['analysts']; // 默认
foreach($groups as $key => $g) {
    if(strpos($g['codes'], $mbti) !== false) { $activeTheme = $g; break; }
}

$careers = json_decode($type['careers'] ?? '[]', true);
$radar = $_SESSION['current_radar'] ?? [70, 60, 80, 50, 90, 75]; // 模拟维度数据
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>你的性格类型是：<?=$type['title']?> (<?=$mbti?>) | MBTI 专业版</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #ffffff; color: #333; font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .banner-clip { clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        .progress-bar { height: 8px; border-radius: 4px; background: #e0e0e0; overflow: hidden; position: relative; }
        .progress-fill { position: absolute; height: 100%; transition: width 1s ease-in-out; }
    </style>
</head>
<body>

    <div class="<?=$activeTheme['bg']?> banner-clip pt-16 pb-32 px-6 text-white relative overflow-hidden">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between relative z-10">
            <div class="md:w-1/2">
                <span class="text-xl opacity-90 mb-2 block tracking-wider">你的人格类型是：</span>
                <h1 class="text-6xl font-black mb-4 tracking-tight"><?=$type['title']?></h1>
                <h2 class="text-3xl font-bold opacity-80 mb-8"><?=$mbti?></h2>
                <p class="text-xl leading-relaxed opacity-90 max-w-lg">
                    "<?=$type['tagline']?>" —— <?=$type['description']?>
                </p>
            </div>
            
            <div class="md:w-1/2 mt-12 md:mt-0 flex justify-center">
                <img src="assets/images/<?=$mbti?>.svg" 
                     alt="Illustration" class="w-80 h-80 drop-shadow-2xl">
                </div>
        </div>
        
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 -rotate-12 translate-x-32 -translate-y-32"></div>
    </div>

    <div class="max-w-6xl mx-auto px-6 -mt-16 relative z-20">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="lg:w-2/3">
                <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 mb-8">
                    <h3 class="text-3xl font-black mb-10 flex items-center">
                        <span class="w-3 h-10 <?=$activeTheme['bg']?> mr-4 rounded-full"></span>
                        1. 人格特征
                    </h3>
                    
                    <div class="space-y-12">
                        <?php 
                        $traits = [
                            ['label' => '能量', 'left' => '外向', 'right' => '内向', 'val' => $radar[0]],
                            ['label' => '信息', 'left' => '实感', 'right' => '直觉', 'val' => $radar[1]],
                            ['label' => '决策', 'left' => '思考', 'right' => '情感', 'val' => $radar[2]],
                            ['label' => '战术', 'left' => '判断', 'right' => '知觉', 'val' => $radar[3]],
                        ];
                        foreach($traits as $t): 
                        ?>
                        <div>
                            <div class="flex justify-between text-sm font-bold text-gray-500 mb-2 px-1">
                                <span><?=$t['left']?></span>
                                <span class="<?=$activeTheme['text']?>"><?=$t['val']?>% <?=$t['val'] > 50 ? $t['right'] : $t['left']?></span>
                                <span><?=$t['right']?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill <?=$activeTheme['bg']?>" style="width: <?=$t['val']?>%; left: 0;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-16 prose prose-lg max-w-none text-gray-600 leading-loose">
                        <p class="mb-6"><?=$type['description']?></p>
                        </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 mb-8">
                    <h3 class="text-3xl font-black mb-10 flex items-center">
                        <span class="w-3 h-10 <?=$activeTheme['bg']?> mr-4 rounded-full"></span>
                        2. 你的职业道路
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach($careers as $c): ?>
                        <div class="<?=$activeTheme['light']?> p-6 rounded-xl border-l-4 border-<?=$activeTheme['bg']?> shadow-sm">
                            <span class="text-2xl mb-2 block">🚀</span>
                            <div class="font-bold text-lg"><?=$c?></div>
                            <div class="text-sm text-gray-500 mt-2">点击查看深度行业匹配分析...</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="lg:w-1/3">
                <div class="sticky top-8 space-y-6">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 flex items-center gap-4">
                            <img src="assets/images/<?=$mbti?>.svg" class="w-12 h-12 bg-gray-100 rounded-lg">
                            <div>
                                <div class="text-xs text-gray-400 font-bold uppercase tracking-wider">你的类型是</div>
                                <div class="font-black text-lg <?=$activeTheme['text']?>"><?=$type['title']?></div>
                            </div>
                        </div>
                        <nav class="p-4">
                            <?php 
                            $menu = ['人格特征', '你的职业道路', '你的个人成长', '你的人际关系'];
                            foreach($menu as $i => $m): 
                            ?>
                            <a href="#" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all font-medium text-gray-600 group">
                                <span><?=($i+1)?>. <?=$m?></span>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                            </a>
                            <?php endforeach; ?>
                        </nav>
                        <div class="p-4 pt-0">
                            <button class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:scale-[1.02] transition-transform">
                                🔓 解锁全部深度结果
                            </button>
                        </div>
                    </div>
                    
                    <button onclick="window.print()" class="w-full bg-white border-2 border-gray-200 text-gray-700 py-4 rounded-xl font-bold hover:bg-gray-50 transition-all">
                        📄 下载 PDF 报告
                    </button>
                </div>
            </div>
            
        </div>
    </div>

    <footer class="py-20 text-center text-gray-400 text-sm">
        <p>© 2025 MBTI 性格研究实验室. 报告编号: <?=$sn?></p>
    </div>

</body>
</html>